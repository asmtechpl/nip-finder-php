<?php
declare(strict_types=1);
namespace NipFinder\Contract;
use NipFinder\DTO\ContractorData;
use NipFinder\ValueObject\Nip;
interface ContractorLookup
{
    public function findByNip(Nip $nip): ContractorData;
}
