<?php

namespace App\Contracts;

interface Auditable
{
    /**
     * Return the fields to capture in a before/after audit snapshot.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array;
}