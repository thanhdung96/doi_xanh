<?php

namespace App\Trait;

trait TimestampTrait
{
    public function getTimestamp(): string{
        return (new \Datetime())->format('Y_m_d_H_i_s');
    }
}