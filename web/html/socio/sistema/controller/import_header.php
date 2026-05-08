<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b style="font-family: 'Josefin Sans', sans-serif;">SAGA</b></span>
      <!-- logo for regular state and mobile devices -->
      <span style="font-family: 'Josefin Sans', sans-serif;" class="logo-lg"><?php echo($nome); ?></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          
          <!-- Notifications: style can be found in dropdown.less -->
          <!-- Tasks: style can be found in dropdown.less -->
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
          <?php
          try {
						if(isset($_SESSION['id_pessoa']) and !empty($_SESSION['id_pessoa'])){
							$foto = $pessoa['imagem'];
							if($foto != null and $foto != "")
								$foto = 'data:image;base64,'.$foto;
                if(preg_match('/^data:image\/[a-zA-Z]+;base64,/', $foto))
                  $foto = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');
							else $foto = WWW."img/semfoto.png";
						}
					} catch (Exception $e) {
            error_log($e->getMessage());
            $foto = WWW . "img/semfoto.png";
          }
					?>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo($foto); ?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo($nome); ?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo($foto); ?>" class="img-circle" alt="User Image">

                <p>
                  <?php echo($nome); ?>
                  <!-- <small>Member since Nov. 2012</small> -->
                </p>
              </li>
              <!-- Menu Body -->
              <li class="user-body">
                <!-- /.row -->
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" id="btn_perfil" class="btn btn-default btn-flat">Perfil</a>
                </div>
                <div class="pull-right">
                  <a href="../sair.php" class="btn btn-default btn-flat">Sair</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <!-- <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li> -->
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->