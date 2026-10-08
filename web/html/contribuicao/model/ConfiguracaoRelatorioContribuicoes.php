<?php
class ConfiguracaoRelatorioContribuicoes
{
    private int|array $periodo = 1;
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
    public function setPeriodo(int|array $periodo)
    {
        if (is_int($periodo) && ($periodo < 1 || $periodo > 8)) {
            throw new InvalidArgumentException(
                'A opção de periodo informada é inválida', 
                400
            );
        }

        if (is_array($periodo)) {
            if (!array_key_exists('inicio', $periodo) || !array_key_exists('fim', $periodo)) {
                throw new InvalidArgumentException(
                    'O período informado não possui uma data de início ou de fim',
                    400
                );
            }

            $dataInicio = DateTime::createFromFormat('Y-m-d', $periodo['inicio'], new DateTimeZone(date_default_timezone_get()));
            $dataFim = DateTime::createFromFormat('Y-m-d', $periodo['fim'], new DateTimeZone(date_default_timezone_get()));

            if (!$dataInicio || $dataInicio->format('Y-m-d') !== $periodo['inicio']) {
                throw new InvalidArgumentException(
                    'A data de início é inválida.',
                    400
                );
            }

            if (!$dataFim || $dataFim->format('Y-m-d') !== $periodo['fim']) {
                throw new InvalidArgumentException(
                    'A data de fim é inválida.',
                    400
                );
            }

            $periodo['inicio'] = $dataInicio;
            $periodo['fim'] = $dataFim;

            if ($periodo['inicio'] > $periodo['fim']) {
                throw new InvalidArgumentException(
                    'A data de início não pode ser maior que a data de fim.',
                    400
                );
            }
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
