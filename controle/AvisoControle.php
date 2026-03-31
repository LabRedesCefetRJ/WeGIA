<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Aviso.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AvisoNotificacaoControle.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AvisoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoService.php';

class AvisoControle
{
    /**
     * Extrai as informações enviadas pelo formulário via POST e realiza as operações necessárias para criar um aviso e enviar suas respectivas notificações para os funcionários cadastrados no sistema
     */
    public function incluir()
    {
        //sanitizar parâmetros
        $idFuncionario = filter_input(INPUT_POST, 'idfuncionario', FILTER_SANITIZE_NUMBER_INT);
        $idPessoaAtendida = filter_input(INPUT_POST, 'idpaciente', FILTER_SANITIZE_NUMBER_INT);
        $idfichamedica = filter_input(INPUT_POST, 'idfichamedica', FILTER_SANITIZE_NUMBER_INT);
        $descricao = filter_input(INPUT_POST, 'descricao_emergencia', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            if (!$idfichamedica || $idfichamedica < 1) {
                throw new InvalidArgumentException('Erro, o id da ficha médica não pode ser menor que 1.', 400);
            }

            if (!$idPessoaAtendida || $idPessoaAtendida < 1) {
                throw new InvalidArgumentException('Erro, o id de um atendido não pode ser menor que 1.', 400);
            }

            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('Erro, o id de um funcionário não pode ser menor que 1.', 400);
            }

            if (!$descricao || strlen($descricao) < 1) {
                throw new InvalidArgumentException('Erro, a descrição informada não é válida.', 400);
            }

            $servicePlantao = new SaudeEquipePlantaoService();
            $plantaoDia = $servicePlantao->resolverPlantaoPorData();
            $idEquipePlantao = $plantaoDia['id_equipe_plantao'] ?? null;
            $idEscalaDia = $plantaoDia['id_escala_dia'] ?? null;
            $dataPlantao = $plantaoDia['data_plantao'] ?? null;
            $turnoPlantao = $plantaoDia['turno'] ?? null;

            $aviso = new Aviso($idFuncionario, $idPessoaAtendida, $descricao, $idEquipePlantao, $idEscalaDia, $dataPlantao, $turnoPlantao);

            $avisoNotificacaoControle = new AvisoNotificacaoControle();

            $avisoDAO = new AvisoDAO();
            $ultimaInsercao = $avisoDAO->cadastrar($aviso);
            if (!$ultimaInsercao) {
                throw new LogicException('Falha ao conferir lançamento da última inserção', 500);
            } else {
                $aviso->setIdAviso($ultimaInsercao);
                $avisoNotificacaoControle->incluir($aviso);
                header("Location: ../html/saude/cadastrar_intercorrencias.php?id_fichamedica=$idfichamedica");
            }
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function listarIntercorrenciaPorIdDaFichaMedica()
    {
        header('Content-Type: application/json');
        try {
            $idfichamedica = filter_input(INPUT_GET, 'id_fichamedica', FILTER_SANITIZE_NUMBER_INT);
            $idEquipePlantao = filter_input(INPUT_GET, 'id_equipe_plantao', FILTER_SANITIZE_NUMBER_INT);

            if (!$idfichamedica || $idfichamedica < 1) {
                throw new InvalidArgumentException('Erro, o id da ficha médica não pode ser menor que 1.', 400);
            }

            $avisoDAO = new AvisoDAO();
            $intercorrencias = $avisoDAO->listarIntercorrenciaPorIdDaFichaMedica($idfichamedica, $idEquipePlantao ?: null);
            $servicePlantao = new SaudeEquipePlantaoService();
            $intercorrencias = $servicePlantao->enriquecerIntercorrenciasComEquipe($intercorrencias);

            foreach ($intercorrencias as $key => $value) {
                $data = new DateTime($value['data']);
                $intercorrencias[$key]['data'] = $data->format('d/m/Y H:i:s');
                $intercorrencias[$key]['descricao'] = htmlspecialchars(html_entity_decode($value['descricao'], ENT_QUOTES, 'UTF-8'));
                $intercorrencias[$key]['equipe_nome'] = htmlspecialchars((string) ($value['equipe_nome'] ?? 'Não definida'), ENT_QUOTES, 'UTF-8');
                $intercorrencias[$key]['equipe_membros'] = htmlspecialchars((string) ($value['equipe_membros'] ?? ''), ENT_QUOTES, 'UTF-8');
                $intercorrencias[$key]['turno_label'] = htmlspecialchars((string) ($value['turno_label'] ?? ''), ENT_QUOTES, 'UTF-8');
            }

            echo json_encode($intercorrencias);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
