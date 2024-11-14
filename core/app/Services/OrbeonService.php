<?php

namespace App\Services;

use App\Utils\CookieParser;
use App\Utils\HTMLProcessor;
use DOMElement;
use DOMException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

interface OrbeonServiceContract
{
    //Form definitions
    public function render(string $app, string $form, string|null $docId, array|null $preselectContext): array;

    public function builder(string $app, string $docId): array;

    //Form data
    public function saveFormData(string $app, string $form, string $document, string $data, bool $final = true): array;

    //Static
    public function getResource(string $path, string $session): array;

    public function postResource(string $path, string $session, string $body): array;

    //Submissions
    public function submitFormValue(string $session, string $inputId, string $value, string $uuid, int $sequence): array;
}

/**
 * Class Orbeon_service
 *
 * This class is responsible for interacting with the Orbeon Forms API.
 */
readonly class OrbeonService implements OrbeonServiceContract
{
    public function __construct(
        private Client $client
    )
    {
    }

    /**
     * The value of the header must be valid JSON, and follow the format described below. An example
     * {
     *      "username": "ljohnson",
     *      "groups": [ "employee" ],
     *      "roles": [
     *          { "name": "Power User" },
     *          { "name": "Full-time" },
     *          { "name": "Manager", "organization": "iOS" },
     *          { "name": "Scrum master", "organization": "Engineering" }
     *      ],
     *      "organizations": [
     *          [ "Acme", "Engineering", "iOS" ],
     *          [ "Acme", "Support" ]
     *      ]
     * }
     * Auth header is X-Orbeon-Auth.
     */
    private function getAuthHeaderFromUser(): array
    {
        $user = auth()->user();

        return [
            'Orbeon-Credentials' => json_encode([
                /**
                 * username is mandatory.
                 */
                'username' => $user->username ?? 'anonymous',
                /**
                 * groups is optional. If present, its value must be an array with one string,
                 * representing the user's group. (An array is used here as we can envision futures
                 * version of Orbeon Forms supporting users being part of more than one group.)
                 */
                'groups' => $user->groups ?? ['public'],
                /**
                 * roles is optional. If present, its value must be an array of roles.
                 * Each role is an object with a mandatory name attribute, and an optional
                 * organization attribute. When the latter is present, it ties the role to
                 * the specified organization, for instance: "Linda is the manager of the iOS
                 * organization". For more on the latter, see Organization-based permissions.
                 */
                'roles' => $user->roles ?? [
                        ['name' => 'public']
                    ],
                /**
                 * organizations is optional. If present, its value must be an array.
                 * Each element of the array must in turn be an array, in which the
                 * last element is the organization the user is a member of, and
                 * preceding elements list where that organization is in the organization
                 * hierarchy. For instance, ["Acme", "Engineering", "iOS"] signifies
                 * that the user is a member of the "iOS" organization, and that, in the
                 * organization hierarchy, "iOS" is a child organization of "Engineering",
                 * and "Engineering" is a child organization of "Acme".
                 */
                'organizations' => $user->organization ?? [],
            ])
        ];
    }

    /**
     * Rendering a form is done via a GET request to the /fr/{app}/{form}/new endpoint.
     *
     * @param string      $app
     * @param string      $form
     * @param string|null $docId
     * @param array{
     *     data: string,
     *     control: string,
     * } | null        $preselectContext
     *  - preselect context is needed so we can preselect values in the form's select controls
     * @return array<string, string>
     * @throws OrbeonException
     */
    public function render(string      $app,
                           string      $form,
                           string|null $docId = null,
                           array       $preselectContext = null
    ): array
    {
        try {
            $config = [
                'headers' => [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding' => 'gzip, deflate, br, zstd',
                    'Cache-Control' => 'no-cache',
                    'Connection' => 'keep-alive',
                    'Host' => request()->getHttpHost(),
                    'Pragma' => 'no-cache',
                ]
            ];

            if (!$docId) {
                $response = $this->client->request('GET', "/orbeon/fr/$app/$form/new", $config);
            } else {
                $response = $this->client->request('GET', "/orbeon/fr/$app/$form/edit/$docId", $config);
            }

            $htmlProcessor = new HTMLProcessor($response->getBody()->getContents());

            if ($preselectContext) {
                [
                    'uuid' => $uuid,
                    'jsessionId' => $jsessionId
                ] = $this->getGetSessionContextFromHtml($htmlProcessor, $response);

                $selectElement = $htmlProcessor->getElementByXPath("//select[contains(@id, '{$preselectContext['control']}')]");

                if (!$selectElement) {
                    throw new OrbeonException("Select element with control ID '{$preselectContext['control']}' not found.");
                }

                $selectName = $selectElement->getAttribute('name');

                $options = $selectElement->getElementsByTagName('option');
                $value = null;

                foreach ($options as $option) {
                    if ($option instanceof DOMElement && $option->getAttribute('title') == $preselectContext['data']) {
                        $value = $option->getAttribute('value');
                        break;
                    }
                }

                if ($value === null) {
                    throw new OrbeonException("Option with title '{$preselectContext['data']}' not found.");
                }


                $this->submitFormValue(
                    $jsessionId,
                    $selectName,
                    $value,
                    $uuid
                );
            }


            $htmlProcessor->removeElementsByClassName('fr-orbeon-version');
            $htmlProcessor->removeElementsByClassName('fr-pdf-button');
            $htmlProcessor->removeElementsByClassName('fr-review-button');
            $htmlProcessor->removeElementsByClassName('fr-summary-button');

            return [
                'html' => $htmlProcessor->getHTML(),
                'cookies' => CookieParser::parseFromResponse($response->getHeader('Set-Cookie'))
            ];
        } catch (GuzzleException $e) {
            throw OrbeonException::fromHttpStatusCode($e->getCode())->withDetail($e->getMessage());
        }
    }

    /**
     * Renders ui for creating a new form for specified app.
     *
     * @throws OrbeonException
     */
    public function builder(string $app, string $docId = null): array
    {
        try {
            $reqConfig = [
                'headers' => [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding' => 'gzip, deflate, br, zstd',
                    'Cache-Control' => 'no-cache',
                    'Connection' => 'keep-alive',
                    'Host' => request()->getHttpHost(),
                    'Pragma' => 'no-cache',
                ]
            ];
            $isNewForm = $docId === null;

            //If we are requesting to edit existing form
            if (!$isNewForm) {
                $response = $this->client->request('GET', "/orbeon/fr/orbeon/builder/edit/$docId", $reqConfig);
            } else {
                $response = $this->client->request('GET', "/orbeon/fr/orbeon/builder/new", $reqConfig);
            }

            $htmlProcessor = new HTMLProcessor($response->getBody()->getContents());
            $htmlProcessor->removeElementsByClassName('fr-orbeon-version');

            //We want to preset values only for new forms
            if ($isNewForm) {
                [
                    'uuid' => $uuid,
                    'jsessionId' => $jsessionId
                ] = $this->getGetSessionContextFromHtml($htmlProcessor, $response);

                $this->submitFormValue(
                    $jsessionId,
                    'dialog-form-settings≡fb-tabbable≡xf-1989≡fb-app-name-input',
                    $app,
                    $uuid
                );
            }

            //But we want to make app name readonly for existing forms also
            $appEl = $htmlProcessor->getElement(
                '//*[@id="dialog-form-settings≡fb-tabbable≡xf-1989≡fb-app-name-input≡xforms-input-1"]'
            );
            $appEl->setAttribute('readonly', 'true');

            return [
                'html' => $htmlProcessor->getHTML(),
                'cookies' => CookieParser::parseFromResponse($response->getHeader('Set-Cookie'))
            ];
        } catch (GuzzleException $e) {
            throw OrbeonException::fromHttpStatusCode($e->getCode())->withDetail($e->getMessage());
        }
    }

    /**
     * Saving form data to Orbeon Forms is done via a PUT request to the /fr/service/{app}/{form}/data/{document}
     * endpoint.
     *
     * @param string $app
     * @param string $form
     * @param string $document
     * @param string $data
     * @param bool   $final
     * @return array<string, string>
     * @throws OrbeonException
     */
    public function saveFormData(string $app, string $form, string $document, string $data, bool $final = true): array
    {
        try {
            $response = $this->client->request('PUT',
                "/fr/service/$app/$form/" . ($final ? 'data' : 'draft') . "/$document/",
                [
                    'headers' => array_merge([
                        'Content-Type' => 'application/xml',
                        'Orbeon-Workflow-Stage' => 'final',
                        'Orbeon-Form-Definition-Version' => '1.0',
                    ], $this->getAuthHeaderFromUser()),
                    'json' => $data,
                    'query' => [
                        'final' => $final
                    ]
                ]);

            return [
                'html' => $response->getBody()->getContents(),
                'cookies' => CookieParser::parseFromResponse($response->getHeader('Set-Cookie'))
            ];
        } catch (GuzzleException $e) {
            throw OrbeonException::fromHttpStatusCode($e->getCode())->withDetail($e->getMessage());
        }
    }

    /**
     * Fetch a resource from Orbeon Forms.
     *
     * @param string $path
     * @param string $session
     * @return array
     * @throws OrbeonException
     */
    public function getResource(string $path, string $session): array
    {
        try {
            $response = $this->client->request('GET', '/orbeon/' . $path, [
                'headers' => [
                    'Cookie' => "JSESSIONID=" . $session
                ]
            ]);

            return [
                'content' => $response->getBody()->getContents(),
                'content-type' => $response->getHeader('Content-Type')
            ];
        } catch (GuzzleException $e) {
            throw OrbeonException::fromHttpStatusCode($e->getCode())->withDetail($e->getMessage());
        }
    }

    /**
     * Post a resource to Orbeon Forms.
     *
     * @param string $path
     * @param string $session
     * @param string $body
     * @return array
     * @throws OrbeonException
     */
    public function postResource(string $path, string $session, string $body): array
    {
        try {
            $response = $this->client->request('POST', '/orbeon/' . $path, [
                'headers' => [
                    'Cookie' => "JSESSIONID=" . $session
                ],
                'body' => $body
            ]);

            return [
                'content' => $response->getBody()->getContents(),
                'content-type' => $response->getHeader('Content-Type')
            ];
        } catch (GuzzleException $e) {
            throw OrbeonException::fromHttpStatusCode($e->getCode())->withDetail($e->getMessage());
        }
    }

    /**
     * Every time user interacts with an orbeon form such as the one for
     * specifying the form settings, call on x-forms api is done. If we want
     * to preset some values, we have also have to post it to the x-forms api.
     * This method is used to build the payload for the form settings form.
     *
     * @param string $session
     * @param string $inputId
     * @param string $value
     * @param string $uuid
     * @param int    $sequence
     * @return array
     * @throws OrbeonException
     */
    public function submitFormValue(
        string $session,
        string $inputId,
        string $value,
        string $uuid,
        int    $sequence = 1
    ): array
    {
        $eventRequest = <<<XML
            <!DOCTYPE xxf:event-request [
            <!ENTITY nbsp "&#160;">
            ]>
            <xxf:event-request xmlns:xxf="http://orbeon.org/oxf/xml/xforms">
                <xxf:uuid>{$uuid}</xxf:uuid>
                <xxf:sequence>{$sequence}</xxf:sequence>
                <xxf:action>
                    <xxf:event name="xxforms-value" source-control-id="{$inputId}">
                        <xxf:property name="value">{$value}</xxf:property>
                    </xxf:event>
                </xxf:action>
            </xxf:event-request>
        XML;

        return $this->postResource('xforms-server', $session, $eventRequest);
    }

    /**
     * This method extracts the session UUID from the script element that
     * contains the dynamic script for the form builder/runner.
     *
     * @param HTMLProcessor $el
     * @return string|null
     */
    private function getSessionUUID(
        HTMLProcessor $htmlProcessor,
    ): string|null
    {
        $el = $htmlProcessor->getScriptElementsBySrc('/orbeon/xforms-server/form/dynamic/')[0];
        $scriptSrc = $el->getAttribute('src');
        $parts = explode('/dynamic/', $scriptSrc);

        $uuid = null;

        if (count($parts) > 1) {
            $uuid = explode('.js', $parts[1])[0];
        }

        return $uuid;
    }

    /**
     * @param HTMLProcessor     $htmlProcessor
     *  - with loaded HTML from response
     * @param ResponseInterface $response
     *  - direct response from Orbeon Forms
     * @return array
     */
    private function getGetSessionContextFromHtml(HTMLProcessor $htmlProcessor, ResponseInterface $response): array
    {
        $uuid = $this->getSessionUUID($htmlProcessor);

        $sessionCookie = $response->getHeader('Set-Cookie')[0];
        $parts = explode(';', $sessionCookie);
        $jsessionId = explode('=', $parts[0])[1];

        return [
            'uuid' => $uuid,
            'jsessionId' => $jsessionId
        ];
    }
}

class OrbeonException extends Exception
{
    public string $detail = '';

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function withDetail(string $detail): self
    {
        $this->detail = $detail;
        return $this;
    }

    static public function fromHttpStatusCode(int $statusCode): self
    {
        return match ($statusCode) {
            401, 440 => self::unauthorized(),
            404 => self::formNotFound(),
            403 => self::forbidden(),
            default => self::unexpectedError(),
        };
    }

    static public function formNotFound(): self
    {
        return new self("Form not found", 404);
    }

    static public function forbidden(): self
    {
        return new self("Forbidden", 403);
    }

    static public function unauthorized(): self
    {
        return new self("Unauthorized", 401);
    }

    static public function unexpectedError(): self
    {
        return new self("Unexpected error", 500);
    }
}

