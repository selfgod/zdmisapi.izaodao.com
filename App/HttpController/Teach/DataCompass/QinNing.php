<?php

namespace App\HttpController\Teach\DataCompass;

use App\Domain\Teach\DataCompass\QingNingDomain;
use Base\BaseController;

class QinNing extends BaseController
{
    protected function getRules()
    {
        return [
            'dataSas' => [
                'openIds' => [
                    'type' => 'array',
                    'require' => TRUE,
                    'format' => 'explode',
                    'separator' => ','
                ],
                'ignore_sign' => TRUE,
                'ignore_auth' => TRUE,
            ]
        ];
    }

    public function dataSas()
    {
        $res = (new QingNingDomain())->getDataSas($this->params['openIds']);
        $this->returnJson($res);
    }
}