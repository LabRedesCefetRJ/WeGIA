<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AvisoNotificacao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AvisoNotificacaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class AvisoNotificacaoControle
{
     /**
      * Recebe como parâmetro um aviso, instância dois objetos, um do tipo AvisoNotificacao e outro do tipo AvisoNotificacaoDAO, chamando o método cadastrar deste último.
      */
     public function incluir($aviso)
     {

          $avisoNotificacao = new AvisoNotificacao($aviso);

          try {
               $avisoNotificacaoDAO = new AvisoNotificacaoDAO();
               $avisoNotificacaoDAO->cadastrar($avisoNotificacao);
          } catch (PDOException $e) {
               Util::tratarException($e);
          }
     }

     /**
      * Recebe como parâmetro o id de uma pessoa, retorna o resultado do método buscarRecentes de um objeto do tipo AvisoNotificacaoDAO
      */
     public function listarRecentes($idPessoa)
     {
          try {
               $avisoNotificacaoDAO = new AvisoNotificacaoDAO();
               $recentes = $avisoNotificacaoDAO->buscarRecentes($idPessoa);
               return $recentes;
          } catch (PDOException $e) {
               Util::tratarException($e);
          }
     }

     /**
      * Recebe como parâmetro o id de uma pessoa, retorna o resultado do método buscarHistoricos de um objeto do tipo AvisoNotificacaoDAO
      */
     public function listarHistoricos($idPessoa)
     {
          try {
               $avisoNotificacaoDAO = new AvisoNotificacaoDAO();
               $historicos = $avisoNotificacaoDAO->buscarHistoricos($idPessoa);
               return $historicos;
          } catch (PDOException $e) {
               Util::tratarException($e);
          }
     }

     /**
      * Recebe como parâmetro o id de uma pessoa, e retorna a quantidade de notificações recentes que essa pessoa possuí
      */
     public function quantidadeRecentes($idPessoa)
     {
          try {
               $avisoNotificacaoDAO = new AvisoNotificacaoDAO();
               $recentesQuantidade = $avisoNotificacaoDAO->contarRecentes($idPessoa);
               return $recentesQuantidade;
          } catch (PDOException $e) {
               Util::tratarException($e);
          }
     }

     /**
      * Extraí via POST o id de uma notificação, e chama o método alterarStatus de um objeto do tipo AvisoNotificacaoDAO
      */
     public function mudarStatus()
     {
          $idNotificacao = filter_input(INPUT_POST, 'id_notificacao', FILTER_SANITIZE_NUMBER_INT);

          try {
               if (!$idNotificacao || $idNotificacao < 1) {
                    throw new InvalidArgumentException('O id de uma notificação informado é inválido.', 400);
               }

               $avisoNotificacaoDAO = new AvisoNotificacaoDAO();
               $avisoNotificacaoDAO->alterarStatus($idNotificacao);
               header("Location: ../html/saude/intercorrencia_visualizar.php");
          } catch (Exception $e) {
               Util::tratarException($e);
          }
     }
}
