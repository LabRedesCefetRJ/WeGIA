<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ContribuicaoLog.php';

class ContribuicaoLogCollection implements IteratorAggregate
{
    private array $logs = [];

    public function __construct(array $logs = [])
    {
        foreach ($logs as $log) {
            $this->add($log);
        }
    }

    public function add(ContribuicaoLog $log)
    {
        $this->logs[] = $log;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->logs);
    }

    public function findByCodigo(string $codigo): ?ContribuicaoLog
    {
        foreach ($this->getIterator() as $log) {
            if ($log->getCodigo() === $codigo) {
                return $log; // Retorna imediatamente ao encontrar
            }
        }
        return null;
    }
}
