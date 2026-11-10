<?php
// Fragmento incluído por páginas de socio/sistema que exigem permissao(4, 7),
// mas também acessível direto pela URL (o .htaccess desta pasta está
// desativado): exige o mesmo acesso aqui, como em import_conteudo_graficos.php.
require_once dirname(__FILE__, 5) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'sessaoCookie.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario'])) die("Você não está logado(a).");

require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 4, 7);
?>
<?php
require_once dirname(__FILE__, 5) . DIRECTORY_SEPARATOR . 'config.php';

try {
  $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
} catch (Exception $e) {
  echo "Ocorreu um erro ao se conectar com o db: " . $e->getMessage();
}

?>

<div class="modal fade" id="adicionarSocioModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">
        <div class="box box-info box-solid socioModal">
          <div class="box-header">
            <h3 class="box-title"><i class="fa fa-user-plus"></i> Novo sócio</h3>
          </div>
          <div class="box-body">
            <form id="frm_novo_socio" action="./cadastro_socio.php" method="POST">
              <?= Csrf::inputField() ?>
              <div class="row">
                <div class="form-group col-xs-3">
                  <label for="pessoa">Tipo de pessoa <span class="text-danger">*</span></label>
                  <select class="form-control" name="pessoa" id="pessoa" required>
                    <option value="fisica">Física</option>
                    <option value="juridica">Jurídica</option>
                  </select>
                </div>
                <div class="form-group col-xs-8 cpf_div">
                  <label id="label_cpf_cnpj" for="valor">CPF <span class="text-danger">*</span></label>

                  <div class="inline-fields">
                    <input type="text" class="form-control" id="cpf_cnpj" name="cpf">

                    <div class="form-check">
                      <input type="checkbox" class="form-check-input" id="check_veri_cpf">
                      <label class="form-check-label" for="exampleCheck1">Desligar verificação de documento</label>
                    </div>
                  </div>
                </div>

              </div>
              <div class="row">
                <div class="form-group mb-2 col-xs-6">
                  <label for="socio_nome">Nome <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="socio_nome" name="socio_nome" placeholder="" required>
                </div>

                <div class="form-group mb-2 col-xs-6">
                  <label for="socio_sobrenome">Sobrenome <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="socio_sobrenome" name="socio_sobrenome" placeholder="" required>
                </div>

              </div>
              <div class="row">
                <div class="form-group col-xs-6">
                  <label for="obs">E-mail</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="">
                </div>
                <div class="form-group col-xs-6">
                  <label for="valor">Telefone</label>
                  <input type="tel" min="0" class="form-control" id="telefone" name="telefone">
                </div>
              </div>
              <div class="row">
                <div class="form-group col-xs-6">
                  <label for="pessoa">Periodiciade (Contribuinte) <span class="text-danger">*</span></label>
                  <select class="form-control" name="contribuinte" id="contribuinte" required>
                    <option value="mensal">Mensal</option>
                    <option value="bimestral">Bimestral</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="semestral">Semestral</option>
                    <option value="casual">Casual (avulso)</option>
                  </select>
                </div>
                <div class="form-group col-xs-6">
                  <label for="valor">Data de nascimento</label>
                  <input type="date" class="form-control" id="data_nasc" name="data_nasc" min="1900-01-01" max="<?= date('Y-m-d') ?>">
                </div>
              </div>
              <div class="row">
                <div class="form-group col-xs-6">
                  <label for="pessoa">Status <span class="text-danger">*</span></label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="" disabled selected>Selecionar Status</option>
                    <?php
                    if ($conexao) {
                      $stmt = $conexao->prepare("SELECT id_sociostatus, status FROM socio_status ORDER BY id_sociostatus");
                      $stmt->execute();
                      $statuses = $stmt->get_result();
                      while ($row = $statuses->fetch_assoc()) {
                        echo "<option value=" . htmlspecialchars($row['id_sociostatus']) . ">" . htmlspecialchars($row['status']) . "</option>";
                      }
                    }
                    ?>
                  </select>
                </div>
                <div class="form-group col-xs-6" style="margin-top: 1.8em;">
                  <div class="form-check">
                    <label class="form-check-label" for="auto_status_contribuicoes">
                      <input type="checkbox" class="form-check-input" id="auto_status_contribuicoes" name="auto_status_contribuicoes" value="1" checked>
                      Atualizar status com base nas contribuições do sistema
                    </label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-xs-6">
                  <label for="valor">Data referência (ínicio contribuição) <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="data_referencia" name="data_referencia" min="1900-01-01" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group col-xs-6">
                  <label for="valor">Valor/período em R$ <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="valor_periodo" name="valor_periodo" onkeypress="return Onlynumbers(event)" min="0" step="0.01" required>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-xs-12">
                  <label for="valor">Tipo de contribuição <span class="text-danger">*</span></label>
                  <select class="form-control" name="tipo_contribuicao" id="tipo_contribuicao" required>
                    <option value="1">Boleto</option>
                    <option value="2">Cartão de crédito</option>
                    <option value="3">Outros</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div style="margin-bottom:  1em" class="form-group col-xs-12 mb-2">
                  <label for="valor">Grupos <span class="text-danger">*</span></label>
                  <a onclick="adicionar_tag()">
                    <i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
                  </a>
                  <select class="form-control" name="tags[]" id="tags" multiple required size="6" required>
                    <?php
                    $stmt = $conexao->prepare("SELECT * FROM socio_tag");
                    $stmt->execute();
                    $tags = $stmt->get_result();
                    while ($row = $tags->fetch_array(MYSQLI_NUM)) {
                      echo "<option value=" . htmlspecialchars($row[0]) . ">" . htmlspecialchars($row[1]) . "</option>";
                    }

                    ?>
                  </select>
                </div>
              </div>
              <div class="box box-info endereco">
                <div class="box-header with-border">
                  <h3 class="box-title">Endereço</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="form-group mb-2 col-xs-6">
                      <label for="cep">CEP</label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" id="cep" class="form-control" placeholder="" name="cep">
                      </div>
                      <div class="status_cep col-xs-12"></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group mb-2 col-xs-8">
                      <label for="nome_cliente">Rua</label>
                      <input type="text" class="form-control" id="rua" name="rua" placeholder="">
                    </div>
                    <div class="form-group col-xs-4">
                      <label for="data_corte">Número</label>
                      <input type="number" class="form-control" min="0" id="numero" name="numero" placeholder="">
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group mb-2 col-xs-6">
                      <label for="nome_cliente">Complemento</label>
                      <input type="text" class="form-control" id="complemento" name="complemento" placeholder="">
                    </div>
                    <div class="form-group col-xs-6">
                      <label for="data_corte">Bairro</label>
                      <input type="text" class="form-control" id="bairro" name="bairro" placeholder="">
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group mb-2 col-xs-6">
                      <label for="nome_cliente">Estado</label>
                      <input type="text" class="form-control" id="estado" name="estado" placeholder="">
                    </div>
                    <div class="form-group col-xs-6">
                      <label for="data_corte">Cidade</label>
                      <input type="text" class="form-control" id="cidade" name="cidade" placeholder="">
                    </div>
                  </div>
                </div>
                <!-- /.box-body -->
              </div>
          </div>
          <div class="modal-footer">
            <button id="btn_reset" type="reset" class="btn btn-danger">Resetar</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary btn_salvar_socio">Salvar sócio</button>
          </div>
          </form>
        </div>
        <!-- /.box-body -->
        <!-- Loading (remove the following to stop the loading)-->

        <!-- end loading -->
      </div>


    </div>
  </div>
</div>

<!-- Modal configurações -->
<div class="modal fade" id="configModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Configurações</h4>
      </div>
      <div class="modal-body">
        <a href="../configuracao" class="btn btn-app">
          <i class="fa fa-edit"></i> Editar textos
        </a>
        <a class="btn btn-app">
          <i class="fa fa-sliders"></i> Sistema
        </a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal bd -->
<div class="modal fade" id="bdModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Banco de dados</h4>
      </div>
      <div class="modal-body">
        <div class="box box-warning box-solid bd_box">
          <div class="box-header">
            <h3 class="box-title">Opções - BD</h3>
          </div>
          <div class="box-body">
            <a id="btn_deletarSocios" class="btn btn-app">
              <i class="fa fa-user-times"></i> Apagar todos sócios
            </a>
          </div>
          <!-- /.box-body -->
          <!-- Loading (remove the following to stop the loading)-->
          <!-- end loading -->
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal importar -->
<div class="modal fade" id="modal_importar_xlsx" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Importar sócios</h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon fa fa-warning"></i> Atenção!</h4>
          A importação pode demorar alguns minutos, não feche a página.
        </div>
        <div class="box box-warning box_xlsx">
          <div class="box-header with-border">
            <h3 class="box-title">Importar sócios através de arquivo .xlsx</h3>
          </div>
          <div class="box-body box_xlsx">
            <form action="" id="form_xlsx" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="exampleInputFile">Tabela .xlsx</label>
                <input type="file" id="arquivo_xlsx" accept=".xls,.xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" name="arquivo" required>
                <p class="help-block">Envie um arquivo .xlsx para continuar.</p>
              </div>
              <input type="submit" class="btn btn-primary pull-right" name="btn_envia_xlsx">
            </form>
            <!-- /input-group -->
          </div>
          <div class="progress progress-sm active">
            <div class="progress-bar progress-bar-info progress-bar-striped barra_envio" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
              <span class="sr-only">20% Complete</span>
            </div>
          </div>
          <!-- /.box-body -->
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal aniversariantes -->
<div class="modal fade" id="modal_aniversariantes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Sócios aniversariantes</h4>
      </div>
      <div class="modal-body">
        <!-- <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-warning"></i> Lista de sócios: </h4>
                Dê os parabéns!
              </div> -->
        <div class="box box-info box_aniversario">
          <div class="box-header with-border">
            <h3 class="box-title">Opções do relatório</h3>
          </div>
          <div class="box-body box_aniversario">
            <div class="row">
              <div class="form-group col-sm-4">
                <label for="modo_filtro_aniversariantes">Filtrar por</label>
                <select id="modo_filtro_aniversariantes" class="form-control">
                  <option value="mes">Mês</option>
                  <option value="intervalo">Intervalo de datas</option>
                </select>
              </div>
              <div class="form-group col-sm-4" id="filtro_mes_aniversariantes">
                <label for="mes_aniversariantes">Mês dos aniversários</label>
                <select id="mes_aniversariantes" class="form-control">
                  <?php
                  $meses = [
                    1 => 'Janeiro',
                    2 => 'Fevereiro',
                    3 => 'Março',
                    4 => 'Abril',
                    5 => 'Maio',
                    6 => 'Junho',
                    7 => 'Julho',
                    8 => 'Agosto',
                    9 => 'Setembro',
                    10 => 'Outubro',
                    11 => 'Novembro',
                    12 => 'Dezembro'
                  ];
                  foreach ($meses as $numeroMes => $nomeMes) {
                    $selecionado = (int) date('n') === $numeroMes ? ' selected' : '';
                    echo '<option value="' . $numeroMes . '"' . $selecionado . '>' .
                      htmlspecialchars($nomeMes, ENT_QUOTES, 'UTF-8') . '</option>';
                  }
                  ?>
                </select>
              </div>
              <div id="filtro_intervalo_aniversariantes" class="col-sm-8" style="display: none;">
                <div class="row">
                  <div class="form-group col-sm-6">
                    <label for="inicio_aniversariantes">Data inicial</label>
                    <input type="date" id="inicio_aniversariantes" class="form-control">
                  </div>
                  <div class="form-group col-sm-6">
                    <label for="fim_aniversariantes">Data final</label>
                    <input type="date" id="fim_aniversariantes" class="form-control">
                  </div>
                </div>
              </div>
            </div>
            <div id="erro_filtro_aniversariantes" class="text-danger" role="alert" style="display: none;"></div>
          </div>
        </div>
        <div class="box box-success box_aniversario">
          <div class="box-header with-border">
            <h3 class="box-title">Lista de aniversariantes</h3>
          </div>
          <div class="box-body box_aniversario">
            <p id="media_idade_aniversariantes" aria-live="polite"></p>
            <table id="tb_aniversario" class="table table-hover" style="width: 100%">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Telefone</th>
                  <th>Data aniversário</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = mysqli_query(
                  $conexao,
                  "SELECT s.id_socio AS socioid, p.nome, p.sobrenome, p.email, p.telefone, p.data_nascimento
                   FROM socio AS s
                   INNER JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa
                   WHERE p.data_nascimento IS NOT NULL AND p.data_nascimento <> '0000-00-00'"
                );
                $hoje = new DateTimeImmutable('today');
                $dddValidos = [
                  '11',
                  '12',
                  '13',
                  '14',
                  '15',
                  '16',
                  '17',
                  '18',
                  '19',
                  '21',
                  '22',
                  '24',
                  '27',
                  '28',
                  '31',
                  '32',
                  '33',
                  '34',
                  '35',
                  '37',
                  '38',
                  '41',
                  '42',
                  '43',
                  '44',
                  '45',
                  '46',
                  '47',
                  '48',
                  '49',
                  '51',
                  '53',
                  '54',
                  '55',
                  '61',
                  '62',
                  '63',
                  '64',
                  '65',
                  '66',
                  '67',
                  '68',
                  '69',
                  '71',
                  '73',
                  '74',
                  '75',
                  '77',
                  '79',
                  '81',
                  '82',
                  '83',
                  '84',
                  '85',
                  '86',
                  '87',
                  '88',
                  '89',
                  '91',
                  '92',
                  '93',
                  '94',
                  '95',
                  '96',
                  '97',
                  '98',
                  '99'
                ];
                while ($resultado = mysqli_fetch_array($query)) {
                  $dataNascimentoOriginal = $resultado['data_nascimento'];
                  $dataNascimento = DateTimeImmutable::createFromFormat('!Y-m-d', $dataNascimentoOriginal);
                  if (!$dataNascimento || $dataNascimento->format('Y-m-d') !== $dataNascimentoOriginal) {
                    continue;
                  }

                  $id = (int) $resultado['socioid'];
                  $nomeCompleto = trim(($resultado['nome'] ?? '') . ' ' . ($resultado['sobrenome'] ?? ''));
                  $nome = htmlspecialchars($nomeCompleto, ENT_QUOTES, 'UTF-8');
                  $aniversario = $dataNascimento->format('m-d');
                  $dataFormatada = $dataNascimento->format('d/m');
                  $idade = $dataNascimento <= $hoje ? $dataNascimento->diff($hoje)->y : null;
                  $idadeAtributo = $idade === null ? '' : (string) $idade;
                  $idadeTexto = $idade === null ? 'Idade indisponível' : $idade . ' anos';

                  $emailOriginal = trim((string) ($resultado['email'] ?? ''));
                  $email = htmlspecialchars($emailOriginal, ENT_QUOTES, 'UTF-8');
                  $emailHtml = filter_var($emailOriginal, FILTER_VALIDATE_EMAIL)
                    ? "<a href='mailto:$email'>$email</a>"
                    : $email;

                  $telefoneOriginal = trim((string) ($resultado['telefone'] ?? ''));
                  $telefoneDigitos = preg_replace('/\D+/', '', $telefoneOriginal);
                  if (strlen($telefoneDigitos) === 12 || strlen($telefoneDigitos) === 13) {
                    if (substr($telefoneDigitos, 0, 2) === '55') {
                      $telefoneDigitos = substr($telefoneDigitos, 2);
                    }
                  }
                  $ddd = substr($telefoneDigitos, 0, 2);
                  $telefoneValido = in_array($ddd, $dddValidos, true)
                    && in_array(strlen($telefoneDigitos), [10, 11], true);
                  if ($telefoneValido) {
                    $numeroLocal = substr($telefoneDigitos, 2);
                    $telefoneFormatado = strlen($numeroLocal) === 9
                      ? '(' . $ddd . ') ' . substr($numeroLocal, 0, 5) . '-' . substr($numeroLocal, 5)
                      : '(' . $ddd . ') ' . substr($numeroLocal, 0, 4) . '-' . substr($numeroLocal, 4);
                    $telefoneHtml = "<a target='_blank' rel='noopener noreferrer' href='https://wa.me/55$telefoneDigitos'>" .
                      htmlspecialchars($telefoneFormatado, ENT_QUOTES, 'UTF-8') . '</a>';
                  } else {
                    $telefoneHtml = htmlspecialchars($telefoneOriginal, ENT_QUOTES, 'UTF-8');
                  }

                  echo "<tr data-aniversario='$aniversario' data-idade='$idadeAtributo'>";
                  echo "<td onclick='detalhar_socio($id);' style='cursor: pointer'>$nome</td>";
                  echo "<td>$emailHtml</td><td>$telefoneHtml</td>";
                  echo "<td data-order='$aniversario'>$dataFormatada, $idadeTexto &#127874;</td></tr>";
                }
                ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Telefone</th>
                  <th>Data aniversário</th>
                </tr>
              </tfoot>
            </table>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal graficos -->
    <div class="modal fade" id="modal_graficos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span></button>
            <h4 class="modal-title">Aniversariantes</h4>
          </div>
          <div class="modal-body">



          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>