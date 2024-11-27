<?php

namespace App\Integration\Acl\Base\IntegrationSpecific;

use App\Models\Of\OrbeonFormData;
use App\Repositories\Of\OFDataRepository;
use App\Repositories\Of\OrbeonICurrentRepository;
use App\Serializers\OFFormSerializer;
use App\Integration\Acl\Base\ACLShard;
use Illuminate\Support\Facades\DB;

class ACLShard__Orbeon extends ACLShard
{
    public function __construct(
        private readonly OFDataRepository $repository,
        private readonly OrbeonICurrentRepository $orbeonICurrentRepository,

        public readonly string            $app,
        public readonly string            $form,
        array                             $row,
        array                             $requiredFields
    )
    {
        parent::__construct($row, $requiredFields);
    }

    function commit(): OrbeonFormData
    {
        if (count($this->errors) > 0) {
            logger()->warning('Commiting shard with errors: ' . json_encode($this->errors));
        }

        $serialized = $this->serialize();

        return DB::transaction(function () use ($serialized) {
            /** @var OrbeonFormData $inserted */
            $inserted = $this->repository->create([
                'app' => $serialized['app'],
                'form' => $serialized['form'],
                'xml' => $serialized['xml'],
                'document_id' => $serialized['document_id'],
                'created' => $serialized['created'],
                'form_version' => $serialized['form_version'],
                'last_modified_time' => $serialized['last_modified_time'],
                'deleted' => $serialized['deleted'],
                'draft' => $serialized['draft'],
            ]);

            $this->orbeonICurrentRepository->create([
                'data_id' => $inserted->id,
                'document_id' => $inserted->document_id,
                'created' => now(),
                'last_modified_time' => now(),
                'draft' => $inserted->draft,
                'app' => $inserted->app,
                'form' => $inserted->form,
                'form_version' => $inserted->form_version,
                'deleted' => $inserted->deleted,
            ]);

            logger()->info('Shard with id ' . $this->id . ' commited');

            try {
                $shardRepresentation = $this->toRepresentation();
                logger()->info('Shard representation: ' . json_encode($shardRepresentation));
            } catch (\BadMethodCallException $e) {
            }

            return $inserted;
        });
    }

    protected function serialize(): array
    {
        $xml = OFFormSerializer::fromArrayToXmlSubmission($this->data);

        return [
            'created' => now(),
            'last_modified_time' => now(),
            'app' => $this->app,
            'form' => $this->form,
            'xml' => $xml,
            'document_id' => sha1(uniqid(mt_rand(), true)),
            'form_version' => 1,
            'deleted' => 'N',
            'draft' => 'N',
        ];
    }

    function toRepresentation(): array
    {
        throw new \BadMethodCallException("Method not implemented.");
    }
}
