<?php

namespace App\Integration\Acl\Hr\Shards;

use App\Integration\Acl\Base\IntegrationSpecific\ACLShard__Orbeon;
use App\Repositories\Of\OFDataRepository;
use App\Repositories\Of\OrbeonICurrentRepository;

class SubcontractorShard extends ACLShard__Orbeon
{
    public function __construct(
        OFDataRepository $repository,
        OrbeonICurrentRepository $orbeonICurrentRepository,

        array            $row
    )
    {
        parent::__construct(
            $repository,
            $orbeonICurrentRepository,
            "hr",
            "subcontractor",
            $row,
            //TODO: validuj fieldls podla sekcie
            [
                'control-1',
                'control-2',
                'control-3',
                'control-4',
                'control-5',
                'control-6',
                'control-7',
                'control-15',
                'control-16',
                'control-21',
                'control-24',
            ]);

        logger()->info('SubcontractorShard with id ' . $this->id . ' created');

        if (count($this->errors) > 0) {
            logger()->info('SubcontractorShard with id ' . $this->id . ' has errors: ' . json_encode($this->errors));
        }
    }

    function validateInput(): bool
    {
        parent::validateInput();

        if ($this->data['section-1']['control-5'] !== 'M' && $this->data['section-1']['control-5'] !== 'W') {
            $this->errors[] = 'Pohlavie must be M or W';
        }

        if (count($this->errors) > 0) {
            return false;
        }

        return true;
    }

    function toRepresentation(): array
    {
        return [
            'priezvisko' => $this->data['section-1']['control-1'],
            'meno' => $this->data['section-1']['control-2'],
            'priezvisko_za_slobodna' => $this->data['section-1']['control-3'],
            'datum_narodenia' => $this->data['section-1']['control-4'],
            'pohlavie' => $this->data['section-1']['control-5'],
            'rodne_cislo' => $this->data['section-1']['control-6'],
            'zdravotna_poistovna' => $this->data['section-1']['control-7'],
            'miesto_narodenia' => $this->data['section-1']['control-15'],
            'rodinny_stav' => $this->data['section-1']['control-16'],
            'narodnost' => $this->data['section-1']['control-21'],
            'iban' => $this->data['section-1']['control-24'],
        ];
    }
}
