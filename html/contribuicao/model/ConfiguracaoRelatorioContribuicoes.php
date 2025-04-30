<?php
class ConfiguracaoRelatorioContribuicoes
{
    private int $periodo = 1;
    private int $socioId = 0;
    private int $status = 1;

    /**
     * Get the value of periodo
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Set the value of periodo
     *
     * @return  self
     */
    public function setPeriodo(int $periodo)
    {
        if ($periodo < 1 || $periodo > 9) {
            throw new InvalidArgumentException('Valor fornecido é inválido, o número de um período deve estar entre 1 e 9', 400);
        }

        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get the value of socioId
     */
    public function getSocioId()
    {
        return $this->socioId;
    }

    /**
     * Set the value of socioId
     *
     * @return  self
     */
    public function setSocioId($socioId)
    {
        if ($socioId < 0) {
            throw new InvalidArgumentException('Valor fornecido é inválido, o número do id de um sócio não pode ser negativo', 400);
        }
        $this->socioId = $socioId;

        return $this;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */
    public function setStatus($status)
    {
        if ($status < 1 || $status > 4) {
            throw new InvalidArgumentException('Valor fornecido é inválido, o número de um status deve estar entre 1 e 4', 400);
        }


        $this->status = $status;

        return $this;
    }
}
