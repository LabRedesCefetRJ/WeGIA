<section class="body">

  <!-- start: header -->
  <header id="header" class="header">

    <!-- end: search & user box -->
  </header>
  <!-- end: header -->
  <div class="inner-wrapper">
    <!-- start: sidebar -->
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>
    <!-- end: sidebar -->

    <section role="main" class="content-body">
      <header class="page-header">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <h2>Sócios</h2>

        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li>
              <a href="../../home.php">
                <i class="fa fa-home"></i>
              </a>
            </li>
            <li><span>Páginas</span></li>
            <li><span>Sócios</span></li>
          </ol>

          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <!-- start: page -->
      <div class="row">
        <div class="box box-warning">
          <div class="box-header with-border">
            <h3 class="box-title">Controle de sócios</h3>

            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
              </button>
            </div>
            <!-- /.box-tools -->
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <table id="example" class="table table-hover" style="width: 100%">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Telefone</th>
                  <th>Endereço</th>
                  <th>CPF/CNPJ</th>
                  <th>Tipo</th>
                  <th>Observação</th>
                  <th>Editar</th>
                  <th>Deletar</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $fisica = 0;
                $juridica = 0;
                $socios_atrasados = 0;
                $mensal = 0;
                $casual = 0;
                $si_contrib = 0;
                $stmt = $conexao->prepare("SELECT *, s.id_socio AS socioid, sl.descricao AS ultima_descricao_log, sl.data AS ultima_data_socio_log
                          FROM socio AS s
                          LEFT JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa
                          LEFT JOIN socio_tipo AS st ON s.id_sociotipo = st.id_sociotipo
                          LEFT JOIN (
                              SELECT sl.id_socio, sl.descricao, sl.data
                              FROM socio_log AS sl
                              WHERE sl.id IN (SELECT MAX(id) FROM socio_log GROUP BY id_socio)
                          ) AS sl ON sl.id_socio = s.id_socio;");
                $stmt->execute();
                $query = $stmt->get_result();
                while ($resultado = mysqli_fetch_array($query)) {
                  switch ($resultado['id_sociotipo']) {
                    case 0:
                    case 1:
                      $casual++;
                      $contribuinte = "casual";
                      break;
                    case 2:
                    case 3:
                      $mensal++;
                      $contribuinte = "mensal";
                      break;
                    default:
                      $si_contrib++;
                      $contribuinte = "si";
                      break;
                  }

                  $class = "bg-normal";

                  /* Verificar viabilidade de retornar funcionalidade de exibir quantidade de sócios atrasados
                  if ($contribuinte == "mensal") {
                    $data_ultima_doacao = date_create($resultado['ultima_data_doacao']);
                    $data_hoje = date_create();
                    $subtracao_datas = date_diff($data_ultima_doacao, $data_hoje);
                    if ($subtracao_datas->days > 31) {
                      // Adiciona tag vermelha indicando atraso
                      $socios_atrasados++;
                      $class = "bg-danger";
                    }
                  }*/

                  $id = htmlspecialchars($resultado['socioid']);
                  $cpf_cnpj = htmlspecialchars($resultado['cpf']);
                  $nome_s = htmlspecialchars($resultado['nome']);
                  $email = htmlspecialchars($resultado['email']);
                  $telefone = htmlspecialchars($resultado['telefone']);
                  $tipo_socio = htmlspecialchars($resultado['tipo']);
                  if ($resultado['logradouro'] == "") {
                    $endereco = "Endereço não informado/incompleto.";
                  } else {
                    $endereco = htmlspecialchars($resultado['logradouro']) . " " . htmlspecialchars($resultado['numero_endereco']) . ", " . htmlspecialchars($resultado['bairro']) . ", " . htmlspecialchars($resultado['cidade']) . " - " . htmlspecialchars($resultado['estado']);
                  }

                  if (strlen($telefone) == 14) {
                    $tel_url = preg_replace("/[^0-9]/", "", $telefone);
                    $telefone = "<a target='_blank' href='http://wa.me/55$tel_url'>$telefone</a>";
                  }
                  if (strlen($cpf_cnpj) == 14) {
                    $pessoa = "fisica";
                    $fisica++;
                  } else {
                    $pessoa = "juridica";
                    $juridica++;
                  }

                  if ($email == "null") {
                    $email = '';
                  }
                  if ($telefone == "null") {
                    $telefone = '';
                  }

                  //verificar a ultima data de socio_log

                  $ultima_data_log = $resultado['ultima_data_socio_log'];

                  if ($ultima_data_log) {
                    $data_log = new DateTime($ultima_data_log);
                    $data_atual = new DateTime();
                    $intervalo = $data_atual->diff($data_log);

                    // Verifica se a data está dentro dos últimos 30 dias
                    if ($intervalo->days <= 30 && $data_log <= $data_atual) {
                      $socioLog = $resultado['ultima_descricao_log'];
                      //echo "A última entrada no log está dentro dos últimos 30 dias.";
                    } else {
                      $socioLog = 'Nenhuma observação recente';
                      //echo "A última entrada no log não está dentro dos últimos 30 dias.";
                    }
                  } else {
                    $socioLog = 'Nenhuma observação recente';
                    //echo "Nenhuma data de log foi encontrada.";
                  }

                  $del_json = json_encode(array("id" => $id, "nome" => $nome_s, "pessoa" => $pessoa));
                  echo ("<tr><td >$id</td><td onclick='detalhar_socio($id);' style='cursor: pointer' class='$class'>$nome_s</td><td><a href='mailto:$email'>$email</a></td><td>$telefone</td><td>$endereco</td><td>$cpf_cnpj</td><td>$tipo_socio</td><td>$socioLog</td><td><a href='editar_socio.php?socio=$id'><button type='button' class='btn btn-default btn-flat'><i class='fa fa-edit'></i></button></a></td><td><button onclick='deletar_socio_modal($del_json)' type='button' class='btn btn-default btn-flat'><i class='fa fa-remove text-red'></i></button></td></tr>");
                }
                ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>ID</th>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Telefone</th>
                  <th>Endereço</th>
                  <th>CPF/CNPJ</th>
                  <th>Tipo</th>
                  <th>Observação</th>
                  <th>Editar</th>
                  <th>Deletar</th>
                </tr>
              </tfoot>
            </table>
            <?php
            $stmt = $conexao->prepare("select * from socio");
            $stmt->execute();
            $resultado = $stmt->get_result();
            $num_socios = mysqli_num_rows($resultado);
            ?>
            <div class="row">
              <a id="btn_add_socio" class="btn btn-app">
                <span class="badge bg-purple"><span id="qtd_socios"><?php echo ($num_socios); ?></span></span>
                <i class="fa fa-user-plus"></i> Adicionar Sócio
              </a>
              <a id="btn_importar_xlsx" class="btn btn-app">
                <i class="fa fa-upload"></i> Importar sócios
              </a>
              <a onclick="location.reload()" id="btn_atualizar" class="btn btn-app">
                <i class="fa fa-refresh"></i> Atualizar
              </a>
              <a id="btn_aniversariantes" class="btn btn-app">
                <i class="fa fa-birthday-cake"></i> Aniversariantes do mês
              </a>
              <a href="graficos.php" id="btn_graficos" class="btn btn-app">
                <i class="fa fa-chart-area"></i> Gráficos
              </a>
              <a id="btn_bd_off" class="btn btn-app" disabled>
                <i class="fa fa-database"></i> Banco de dados
              </a>
            </div>


          </div>
          <!-- /.box-body -->
        </div>

      </div>
      <!-- end: page -->

      <!-- Verificar viabilidade de retornar funcionalidade de exibir quantidade de sócios atrasados 
       <div class="row"> 
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-red">
            <div class="inner">
              <h3><?php //echo ($socios_atrasados); 
                  ?></h3>
              <p>Sócio(s) com pagamento atrasado.</p>
            </div>
          </div>
        </div>
      </div>-->

    </section>
  </div>
  <aside id="sidebar-right" class="sidebar-right">
    <div class="nano">
      <div class="nano-content">
        <a href="#" class="mobile-close visible-xs">
          Collapse <i class="fa fa-chevron-right"></i>
        </a>
      </div>
    </div>
  </aside>
</section>
</body>
<!-- /.box-body -->