<?php

  // Start the session
  require_once('commonfiles/startsession.php');

  require_once('lib/ctas_appvars.php');
  require_once('lib/connectvars.php');

  require_once('commonfiles/funciones.php');

  // Insert the page header
  $page_title = 'Editar Solicitud - Gestión Cuentas SINDO';
    require_once('lib/header.php');

  // Show the navigation menu
  require_once('lib/navmenu.php');

  $error_msg = "";
  $output_form = 'yes';

  // Make sure the user is logged in before going any further.
  if ( !isset( $_SESSION['id_user'] ) ) {
    echo '<p class="error">Por favor <a href="login.php">inicia sesión</a> para acceder a esta página.</p>';
    require_once('lib/footer.php');
    exit();
  }

  // Connect to the database
  $ResultadoConexion = fnConnectBD( $_SESSION['id_user'],  $_SESSION['ip_address'], 'EQUIPO.' . $_SESSION['host'], 'Conn-EditarSolicitud' );
  if ( !$ResultadoConexion ) {
    // Hubo un error en la conexión a la base de datos;
    printf( " Connect failed: %s", mysqli_connect_error() );
    require_once('lib/footer.php');
    exit();
  }

  $dbc = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

  $query = "SELECT id_user 
            FROM  dspa_permisos
            WHERE id_modulo = 14
            AND   id_user   = " . $_SESSION['id_user'];
  /*echo $query;*/
  $data = mysqli_query($dbc, $query);

  if ( mysqli_num_rows( $data ) == 1 ) {
    // El usuario tiene permiso para éste módulo
  }
  else {
    echo '<p class="advertencia">No tiene permisos activos para este módulo. Por favor contacte al Administrador del sitio. </p>';
    require_once('lib/footer.php');
    $log = fnGuardaBitacora( 5, 106, $_SESSION['id_user'],  $_SESSION['ip_address'], 'CURP:' . $_SESSION['username'] . '|EQUIPO:' . $_SESSION['host'] );
    exit(); 
  }

  if ( isset( $_POST['submit'] ) ) {

    /*$error_msg = fnConnect( $dbc );*/
    $dbc = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

    $id_solicitud =         $_POST['id_solicitud'];
    $cmbValijas =           mysqli_real_escape_string( $dbc, trim( $_POST['cmbValijas'] ) );
    $fecha_solicitud_del =  mysqli_real_escape_string( $dbc, trim( $_POST['fecha_solicitud_del'] ) );
    $cmbtipomovimiento =    mysqli_real_escape_string( $dbc, trim( $_POST['cmbtipomovimiento'] ) );
    $cmbDelegaciones =      mysqli_real_escape_string( $dbc, trim( $_POST['cmbDelegaciones'] ) );
    $cmbSubdelegaciones =   mysqli_real_escape_string( $dbc, trim( $_POST['cmbSubdelegaciones'] ) );
    $primer_apellido =      mysqli_real_escape_string( $dbc, strtoupper( trim( $_POST['primer_apellido'] ) ) );
    $segundo_apellido =     mysqli_real_escape_string( $dbc, strtoupper( trim( $_POST['segundo_apellido'] ) ) );
    $nombre =               mysqli_real_escape_string( $dbc, strtoupper( trim( $_POST['nombre'] ) ) );
    $matricula =            mysqli_real_escape_string( $dbc, strtoupper( trim( $_POST['matricula'] ) ) );
    $curp =                 mysqli_real_escape_string( $dbc, strtoupper( trim( $_POST['curp'] ) ) );
    $usuario =              mysqli_real_escape_string( $dbc, strtoupper( trim( $_POST['usuario'] ) ) );
    $cmbgponuevo =          mysqli_real_escape_string( $dbc, trim( $_POST['cmbgponuevo'] ) );
    $cmbgpoactual =         mysqli_real_escape_string( $dbc, trim( $_POST['cmbgpoactual'] ) );
    $cmbcausarechazo =      mysqli_real_escape_string( $dbc, trim( $_POST['cmbcausarechazo'] ) );
    $comentario =           mysqli_real_escape_string( $dbc, trim( $_POST['comentario'] ) );

    $new_file =             mysqli_real_escape_string( $dbc, trim( $_FILES['new_file']['name'] ) );
    
    $Actualizar_new_file = true;

    if ( $new_file == '' ) {
      /*  echo 'Sin archivo nuevo, usemos el mismo';*/
      $Actualizar_new_file = false;
    }
    
    $new_file_type = $_FILES['new_file']['type'];
    $new_file_size = $_FILES['new_file']['size'];

    $output_form = 'no';
  }

?>
       
          <?php

          if ( isset( $_POST['submit'] ) ) {

            if ( empty( $id_solicitud ) ) {
              echo '<p class="error">No hay ID_SOLICITUD</p>';
              $output_form = 'yes';
            }

            /*if ( empty( $cmbLotes ) ) {
              echo '<p class="error">No hay ID_LOTE</p>';
              $output_form = 'yes';
            }*/

            if ( empty( $cmbValijas ) ) {
              echo '<p class="error">Olvidaste seleccionar una Valija.</p>';
              $output_form = 'yes';
            }
            
            if ( !preg_match( '/^[0-9]{9}$/', $fecha_solicitud_del ) ) {
              $anio = substr( $fecha_solicitud_del, 0, 4 );
              $mes  = substr( $fecha_solicitud_del, 5, 2 );
              $dia  = substr( $fecha_solicitud_del, 8, 2 );
              
              if ( !checkdate( $mes, $dia, $anio ) ) {
                echo '<p class="error">Fecha de la solicitud inválida. ';
                echo 'Año:'  . $anio;
                echo ' Mes:'         . $mes;
                echo ' Día:'  . $dia  . '</p>';
                $output_form = 'yes';
              }

              if ( $anio < 2017 ) {
                echo '<p class="advertencia">El año en Fecha de Área de Gestión no es el actual y Mainframe no lo aceptará. ';
                echo ' Año:'  . $anio . '  Debe rechazarse esta solicitud. </p>';
                /*$output_form = 'yes'; */
              }
            }

            if ( empty( $cmbtipomovimiento ) ) {
              echo '<p class="error">Olvidaste seleccionar un Tipo de Movimiento.</p>';
              $output_form = 'yes';
            }

            if ( empty( $cmbDelegaciones ) || 
                    ( $cmbDelegaciones == 0 ) || 
                    ( $cmbDelegaciones == -1 ) 
                  ) {
              echo '<p class="error">Olvidaste seleccionar una Delegación.</p>';
              $output_form = 'yes';
            }

            // if ( ( empty( $cmbSubdelegaciones ) ) )  {
            
            if ( ( empty( $cmbSubdelegaciones ) || $cmbSubdelegaciones == -1 ) && $cmbSubdelegaciones <> 0 )  {
              echo '<p class="error">Olvidaste seleccionar una Subdelegación.</p>';
              $output_form = 'yes';
            }

            if ( empty( $primer_apellido ) ) {
              echo '<p class="error">Olvidaste capturar el Primer Apellido.</p>';
              $output_form = 'yes';
            }

            if ( empty( $nombre ) ) {
              echo '<p class="error">Olvidaste capturar el Nombre.</p>';
              $output_form = 'yes';
            }

            // BAJA
            if ( $cmbtipomovimiento == 2 ) { 
              if ( empty( $cmbgpoactual ) || ( $cmbgpoactual == 0 ) ) {
              echo '<p class="error">Olvidaste seleccionar el Grupo Actual para una solicitud de BAJA.</p>';
              $output_form = 'yes';
              }
            }

            //Si el tipo de movimiento es diferente a BAJA, no se permiten Matrícula y CURP nulas.
            if ( ( $cmbtipomovimiento <> 2 ) && ( empty( $matricula ) ) ) {
              echo '<p class="error">Olvidaste capturar la Matrícula.</p>';
              $output_form = 'yes';
            }

            if ( ( $cmbtipomovimiento <> 2 ) && ( empty( $curp ) ) ) {
              echo '<p class="error">Olvidaste capturar la CURP.</p>';
              $output_form = 'yes';
            }

            if ( empty( $usuario ) ) {
              echo '<p class="error">Olvidaste capturar Usuario.</p>';
              $output_form = 'yes';
            }

            // ALTA
            if ( $cmbtipomovimiento == 1 ) {
              if ( empty( $cmbgponuevo ) || ( $cmbgponuevo == 0 ) ) {
              echo '<p class="error">Olvidaste seleccionar el Grupo Nuevo para una solicitud de ALTA.</p>';
              $output_form = 'yes';
              }
            }

            // CAMBIO
            if ( $cmbtipomovimiento == 3 ) {
              if ( empty( $cmbgpoactual ) || ( $cmbgpoactual == 0 ) ) {
                echo '<p class="error">Olvidaste seleccionar el Grupo Actual para una solicitud de CAMBIO.</p>';
                $output_form = 'yes';
              }
            }

            if ( $cmbtipomovimiento == 3 ) {
              if ( empty( $cmbgponuevo ) || ( $cmbgponuevo == 0 ) ) {
                echo '<p class="error">Olvidaste seleccionar el Grupo Nuevo para una solicitud de CAMBIO.</p>';
                $output_form = 'yes';
              }
            }

            if ( ( empty( $cmbcausarechazo ) || $cmbcausarechazo  == -1 ) && $cmbcausarechazo <> 0 )  {
              echo '<p class="error">Olvidaste capturar Causa de Rechazo</p>';
              $output_form = 'yes';
            }

            if ( $output_form == 'no' ) {

              $error = false;

              //Si el archivo no es nuevo, no tiene caso realizar las siguientes validaciones
              if ( $Actualizar_new_file ) {

                // Validate and move the uploaded picture file, if necessary
                if ( !empty( $new_file ) ) {

                  if ( ( ( $new_file_type == 'application/pdf' ) || ( $new_file_type == 'image/gif' ) || ( $new_file_type == 'image/jpeg' ) || ( $new_file_type == 'image/pjpeg' ) || ( $new_file_type == 'image/png' ) ) && ( ( $new_file_size > 0 ) && ( $new_file_size <= MM_MAXFILESIZE_VALIJA ) ) ) {
                    if ( $_FILES['new_file']['error'] == 0 ) {
                      $timetime = time();
                      //Move the file to the target upload folder
                      $target = MM_UPLOADPATH_CTASSINDO . $timetime . " " . basename( $new_file );

                        // The new file file move was successful, now make sure any old file is deleted
                      if ( move_uploaded_file( $_FILES['new_file']['tmp_name'], $target ) ) {

                        $error = false; //No hay error, podremos hacer UPDATE (línea 257) y mostrar los resultados
                      }
                      else {
                        // The new picture file move failed, so delete the temporary file and set the error flag
                        @unlink( $_FILES['new_file']['tmp_name'] );
                        $error = true;
                        echo '<p class="error">Lo sentimos, hubo un problema al cargar tu archivo.</p>';
                      } // if ( move_uploaded_file(...

                    } // if ( $_FILES['new_file']['error'] == 0 )...

                  }
                  else {
                  // The new picture file is not valid, so delete the temporary file and set the error flag
                    @unlink( $_FILES['new_file']['tmp_name'] );
                    $error = true;
                    echo '<p class="error">El archivo debe ser PDF, GIF, JPEG o PNG no mayor de '. ( MM_MAXFILESIZE_VALIJA / 1024 ) . ' KB de tamaño.</p>';
                  } // if ( ( ( $new_file_type == 'application/pdf' )...

                } // ELSE de "if ( !empty( $new_file ) )"
                else {
                  $output_form = 'yes';
                }

              } // END IF de "if ( $Actualizar_new_file )"

              if ( !$error ) { //Si no hay error, hacemos el UPDATE y mostramos el registro modificado:

                // Conectarse a la BD
                $dbc = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

                $query = "INSERT INTO ctas_hist_solicitudes (id_solicitud, id_valija, id_lote, fecha_solicitud_del, delegacion, subdelegacion, nombre, primer_apellido, segundo_apellido, matricula, curp, usuario, id_movimiento, id_grupo_nuevo, id_grupo_actual, comentario, id_causarechazo, archivo, id_user) SELECT id_solicitud, id_valija, id_lote, fecha_solicitud_del, delegacion, subdelegacion, nombre, primer_apellido, segundo_apellido, matricula, curp, usuario, id_movimiento, id_grupo_nuevo, id_grupo_actual, comentario, id_causarechazo, archivo, id_user FROM ctas_solicitudes WHERE id_solicitud = " . $id_solicitud . " LIMIT 1";

                mysqli_query( $dbc, $query );

                $log = fnGuardaBitacora( 1, 109, $_SESSION['id_user'],  $_SESSION['ip_address'], 'id_solicitud:' . $id_solicitud . '|CURP:' . $_SESSION['username'] . '|EQUIPO:' . $_SESSION['host'] );

                $query = "UPDATE ctas_solicitudes
                        SET id_valija = '$cmbValijas', 
                            fecha_solicitud_del = '$fecha_solicitud_del',
                            fecha_modificacion = NOW(),
                            delegacion = '$cmbDelegaciones',
                            subdelegacion = '$cmbSubdelegaciones',
                            nombre = '$nombre',
                            primer_apellido = '$primer_apellido',
                            segundo_apellido = '$segundo_apellido',
                            matricula = '$matricula',
                            curp = '$curp',
                            usuario = '$usuario',
                            id_movimiento = '$cmbtipomovimiento',
                            id_grupo_actual = '$cmbgpoactual',
                            id_grupo_nuevo = '$cmbgponuevo',
                            comentario = '$comentario',
                            id_causarechazo = '$cmbcausarechazo',";

                if ( $Actualizar_new_file ) { //...si hay nuevo archivo, sí hay que agregar campo "archivo"
                  $query = $query . "archivo = '$timetime $new_file',";
                }

                $query = $query . "id_user = " . $_SESSION['id_user'] . " WHERE 
                                  id_solicitud = '" . $id_solicitud . "' LIMIT 1";

                /*echo $query;*/
                mysqli_query( $dbc, $query );

                $id_solicitud_bitacora = $id_solicitud;
                $log = fnGuardaBitacora( 2, 106, $_SESSION['id_user'],  $_SESSION['ip_address'], 'id_solicitud:' . $id_solicitud_bitacora . '|CURP:' . $_SESSION['username'] . '|EQUIPO:' . $_SESSION['host'] );

                echo '<p class="mensaje"><strong>¡La solicitud ha sido actualizada!</strong></p>';

                echo '<p class="mensaje">¿Deseas volver a <a href="editarsolicitud.php?id_solicitud=' . $id_solicitud . '">editar la solicitud</a>?</p>';

                echo '</br><p class="mensaje">Puede agregar una <a href="agregarsolicitud.php">nueva solicitud</a></p>';

                echo '<p class="mensaje">O puede regresar al <a href="index.php">inicio</a></p>';

                $query = "SELECT ctas_solicitudes.id_solicitud, ctas_solicitudes.id_valija, 
                      ctas_solicitudes.fecha_captura_ca, ctas_solicitudes.fecha_solicitud_del, ctas_solicitudes.fecha_modificacion, ctas_solicitudes.id_lote,
                      ctas_solicitudes.delegacion, ctas_solicitudes.subdelegacion, 
                      ctas_solicitudes.nombre, ctas_solicitudes.primer_apellido, ctas_solicitudes.segundo_apellido, 
                      ctas_solicitudes.matricula, ctas_solicitudes.curp, ctas_solicitudes.curp_correcta, ctas_solicitudes.cargo, ctas_solicitudes.usuario, 
                      ctas_solicitudes.id_movimiento, ctas_solicitudes.id_grupo_actual, ctas_solicitudes.id_grupo_nuevo, 
                      ctas_solicitudes.comentario, ctas_solicitudes.id_causarechazo, ctas_solicitudes.archivo,
                      CONCAT(dspa_usuarios.nombre, ' ', dspa_usuarios.primer_apellido) AS creada_por
                    FROM ctas_solicitudes, ctas_grupos grupos1, ctas_grupos grupos2, dspa_usuarios
                    WHERE ctas_solicitudes.id_grupo_nuevo= grupos1.id_grupo
                    AND   ctas_solicitudes.id_grupo_actual= grupos2.id_grupo
                    AND   ctas_solicitudes.id_user = dspa_usuarios.id_user ";

                $query = $query . "AND ctas_solicitudes.id_solicitud = '" . $id_solicitud . "'";

                /*echo $query;*/

                $data = mysqli_query( $dbc, $query );

                if ( mysqli_num_rows( $data ) == 1 ) {
                  // The user row was found so display the user data
                  $rowB = mysqli_fetch_array($data);
                }

                ?>

                <div class="contenedor">
                  <form>
                    <h2>Datos de la solicitud</h2>
                    <ul>

                      <li>
                        <input hidden class="textinput" type="text" required name="id_solicitud" id="id_solicitud" value="<?php if ( !empty( $rowB['id_solicitud'] ) ) echo $rowB['id_solicitud']; ?>"/>
                      </li>

                      <li>
                        <label for="cmbValijas">Número de Valija/Oficio</label>
                        <select disabled class="textinput" id="cmbValijas" name="cmbValijas">
                          <?php
                            $query = "SELECT ctas_valijas.id_valija AS id_valija2, 
                                        ctas_valijas.delegacion AS num_del, 
                                        dspa_delegaciones.descripcion AS delegacion_descripcion, 
                                        ctas_valijas.num_oficio_del,
                                        ctas_valijas.num_oficio_ca, 
                                        ctas_valijas.id_user
                                      FROM ctas_valijas, dspa_delegaciones 
                                      WHERE ctas_valijas.delegacion = dspa_delegaciones.delegacion
                                      AND ctas_valijas.id_valija = " . $rowB['id_valija'];
                            
                            /*echo $query;*/

                            $result = mysqli_query( $dbc, $query );
                            while ( $row2 = mysqli_fetch_array( $result ) )
                              echo '<option value="' . $row2['id_valija2'] . '" selected>' . $row2['num_oficio_ca'] . ': ' . $row2['num_del'] . '-' . $row2['delegacion_descripcion'] . '</option>';
                          ?>
                        </select>
                      </li>

                      <li>
                        <label for="fecha_solicitud_del">Fecha solicitud</label>
                        <input disabled type="date" id="fecha_solicitud_del" name="fecha_solicitud_del" value="<?php if (!empty( $rowB['fecha_solicitud_del'] ) ) echo $rowB['fecha_solicitud_del']; ?>" />
                      </li>

                      <li>
                        <label for="cmbtipomovimiento">Tipo de Movimiento</label>
                        <select disabled="" id="cmbtipomovimiento" name="cmbtipomovimiento">
                          <?php
                            $query = "SELECT * 
                                      FROM ctas_movimientos 
                                      WHERE id_movimiento = " . $rowB['id_movimiento'];
                            $result = mysqli_query( $dbc, $query );
                            while ( $row2 = mysqli_fetch_array( $result ) )
                              echo '<option value="' . $row2['id_movimiento'] . '" selected>' . $row2['descripcion'] . '</option>';
                          ?>
                        </select>
                      </li>

                      <li>
                        <label for="cmbDelegaciones">Delegación IMSS</label>
                        <select disabled class="textinput" id="cmbDelegaciones" name="cmbDelegaciones">
                          <?php
                            $query = "SELECT * 
                                      FROM dspa_delegaciones 
                                      WHERE delegacion = " . $rowB['delegacion'];
                            $result = mysqli_query( $dbc, $query );
                            while ( $row2 = mysqli_fetch_array( $result ) )
                              echo '<option value="' . $row2['delegacion'] . '" selected>' . $row2['delegacion'] . ' - ' . $row2['descripcion'] . '</option>';
                          ?>
                        </select>
                      </li>

                      <li>
                        <label for="cmbSubdelegaciones">Subdelegación IMSS</label>
                        <select disabled id="cmbSubdelegaciones" name="cmbSubdelegaciones">
                          <?php
                            $query = "SELECT * 
                                      FROM dspa_subdelegaciones 
                                      WHERE delegacion = " . $rowB['delegacion'] . " AND subdelegacion = " . $rowB['subdelegacion'];
                            $result = mysqli_query( $dbc, $query );
                            while ( $row2 = mysqli_fetch_array( $result ) )
                              echo '<option value="' . $row2['subdelegacion'] . '" selected>' . $row2['subdelegacion'] . ' - ' . $row2['descripcion'] . '</option>';
                            ?>
                          </select>
                      </li>

                      <li>
                        <label for="primer_apellido">Primer apellido</label>
                        <input disabled class="textinput" type="text" name="primer_apellido" id="primer_apellido" value="<?php if ( !empty( $rowB['primer_apellido'] ) ) echo $rowB['primer_apellido']; ?>"/>
                      </li>

                      <li>
                        <label for="segundo_apellido">Segundo apellido</label>
                        <input disabled class="textinput" type="text" name="segundo_apellido" id="segundo_apellido" value="<?php if ( !empty( $rowB['segundo_apellido'] ) ) echo $rowB['segundo_apellido']; ?>"/>
                      </li>

                      <li>
                        <label for="nombre">Nombre(s)</label>
                        <input disabled class="textinput" type="text" name="nombre" id="nombre" value="<?php if ( !empty( $rowB['nombre'] ) ) echo $rowB['nombre']; ?>"/>
                      </li>

                      <li>
                        <label for="matricula">Matrícula</label>
                        <input disabled class="textinput" type="text" name="matricula" id="matricula" value='<?php if ( !empty( $rowB['matricula'] ) ) echo $rowB['matricula']; ?>'/>
                      </li>

                      <li>
                        <label for="curp">CURP (Usuario)</label>
                        <input disabled class="textinput" type="text" name="curp" id="curp" value="<?php if ( !empty( $rowB['curp'] ) ) echo $rowB['curp']; ?>" />
                      </li>

                      <li>
                        <label for="usuario">Usuario</label>
                        <input disabled class="textinput" type="text" name="usuario" id="usuario" value="<?php if ( !empty( $rowB['usuario'] ) ) echo $rowB['usuario']; ?>" />
                      </li>

                      <li>
                        <label for="cmbgpoactual">Grupo Actual</label>
                        <select disabled id="cmbgpoactual" name="cmbgpoactual">
                            <?php
                              $query = "SELECT * 
                                        FROM ctas_grupos 
                                        WHERE id_grupo = " . $rowB['id_grupo_actual'];
                              $result = mysqli_query( $dbc, $query );
                              while ( $row2 = mysqli_fetch_array( $result ) )
                                echo '<option value="' . $row2['id_grupo'] . '" selected>' . $row2['descripcion'] . '</option>';
                            ?>
                          </select>
                      </li>

                      <li>
                        <label for="cmbgponuevo">Grupo Nuevo</label>
                        <select disabled id="cmbgponuevo" name="cmbgponuevo">
                            <?php
                              $query = "SELECT * 
                                        FROM ctas_grupos 
                                        WHERE id_grupo = " . $rowB['id_grupo_nuevo'];
                              $result = mysqli_query( $dbc, $query );
                              while ( $row2 = mysqli_fetch_array( $result ) )
                                echo '<option value="' . $row2['id_grupo'] . '" selected>' . $row2['descripcion'] . '</option>';
                            ?>
                          </select>
                      </li>

                      <li>
                        <label for="cmbcausarechazo">Causa de Rechazo</label>
                        <select disabled class="combo0" id="cmbcausarechazo" name="cmbcausarechazo">
                            <?php
                              $query = "SELECT * 
                                        FROM ctas_causasrechazo
                                        WHERE id_causarechazo = " . $rowB['id_causarechazo'];
                              $result = mysqli_query( $dbc, $query );
                              while ( $row2 = mysqli_fetch_array( $result ) )
                                echo '<option value="' . $row2['id_causarechazo'] . '" selected>' . $row2['id_causarechazo'] . ' - ' . $row2['descripcion'] . '</option>';
                            ?>
                        </select>
                      </li>

                      <li>
                        <label for="comentario">Comentario</label>
                        <textarea disabled class="textarea" id="comentario" name="comentario"><?php if ( !empty( $rowB['comentario'] ) ) echo $rowB['comentario']; ?></textarea>
                      </li>

                      <li>
                        <label for="new_file">Archivo</label>
                        <?php 
                          if ( !empty( $rowB['archivo'] ) ) 
                            echo '<a href="' . MM_UPLOADPATH_CTASSINDO . '\\' . $rowB['archivo'] . '"  target="_new">' . $rowB['archivo'] . '</a>';
                          else echo '(Vacío)';
                        ?>
                      </li>

                      <li>
                          <label for="id_user">Capturada por:</label>
                          <input disabled class="textinputsmall" type="text" name="id_user" id="id_user" value="<?php if ( !empty( $rowB['creada_por'] ) ) echo $rowB['creada_por']; ?>"/>
                        </li>

                        <li>
                          <label for="fecha_modificacion">Fecha de modificación</label>
                          <input disabled class="text" type="text" name="fecha_modificacion" id="fecha_modificacion" value="<?php if ( !empty( $rowB['fecha_modificacion'] ) ) echo $rowB['fecha_modificacion']; ?>"/>
                        </li>
                     
                    </ul>
                  </form>
                </div>

                <?php
                /*}
                else {
                  echo '<p class="error"><strong>La nueva solicitud no ha podido generarse. Contactar al administrador.</strong></p>';
                }*/

                // Clear the score data to clear the form
                $_POST['id_solicitud']    = 0;
                $_POST['cmbLotes']    = 0;
                $_POST['cmbValijas'] = 0;
                $_POST['fecha_solicitud_del'] = "";
                $_POST['fecha_modificacion'] = "";
                $_POST['cmbtipomovimiento'] = 0;
                $_POST['cmbDelegaciones'] = 0;
                $_POST['cmbSubdelegaciones'] = -1;
                $_POST['nombre'] = "";
                $_POST['primer_apellido'] = "";
                $_POST['segundo_apellido'] = "";
                $_POST['matricula'] = "";
                $_POST['curp'] = "";
                $_POST['cargo'] = "";
                $_POST['usuario'] = "";
                $_POST['cmbgpoactual'] = 0;
                $_POST['cmbgponuevo'] = 0;
                $_POST['cmbcausarechazo'] = -1;
                $_POST['comentario'] = "";
                $_POST['new_file'] = "";

                mysqli_close( $dbc );

              } //ENDIF de "if ( !$error )"

            } // ELSE de "if ( $output_form == 'no' )"
            else {
              echo '<p class="error">Debes ingresar todos los datos obligatorios para registrar la solicitud.</p>';
            }

          }
          else {
            echo '<p class="mensaje"><strong>Ahora puedes EDITAR los datos de esta solicitud.</strong></p>';
          }

          ?>
            
    <?php

      if ( $output_form == 'yes' ) {
        $query = "SELECT 
          ctas_solicitudes.id_solicitud, ctas_solicitudes.id_valija, 
          ctas_solicitudes.fecha_captura_ca, ctas_solicitudes.fecha_solicitud_del, ctas_solicitudes.fecha_modificacion, ctas_solicitudes.id_lote,
          ctas_solicitudes.delegacion, ctas_solicitudes.subdelegacion, 
          ctas_solicitudes.nombre, ctas_solicitudes.primer_apellido, ctas_solicitudes.segundo_apellido, 
          ctas_solicitudes.matricula, ctas_solicitudes.curp, ctas_solicitudes.curp_correcta, ctas_solicitudes.cargo, ctas_solicitudes.usuario, 
          ctas_solicitudes.id_movimiento, ctas_solicitudes.id_grupo_actual, ctas_solicitudes.id_grupo_nuevo, 
          ctas_solicitudes.comentario, ctas_solicitudes.id_causarechazo, ctas_solicitudes.archivo, ctas_solicitudes.id_user,
          CONCAT(dspa_usuarios.nombre, ' ', dspa_usuarios.primer_apellido) AS creada_por
          FROM ctas_solicitudes, ctas_grupos grupos1, ctas_grupos grupos2, dspa_usuarios
          WHERE ctas_solicitudes.id_grupo_nuevo = grupos1.id_grupo
          AND   ctas_solicitudes.id_grupo_actual = grupos2.id_grupo
          AND   ctas_solicitudes.id_lote = 0";
        $query = $query . " AND   ctas_solicitudes.id_user = dspa_usuarios.id_user ";

        if ( !isset( $_GET['id_solicitud'] ) ) {
          $query = $query . "AND ctas_solicitudes.id_solicitud = '" . $_SESSION['id_solicitud'] . "'";
          $id_solicitud_bitacora = $_SESSION['id_solicitud'];
          
        } else {
          $query = $query . "AND ctas_solicitudes.id_solicitud = '" . $_GET['id_solicitud'] . "'";
          $id_solicitud_bitacora = $_GET['id_solicitud'];
        }

        /*echo $query;
        echo $id_solicitud_bitacora;*/
        $data = mysqli_query( $dbc, $query );
        $rowF = mysqli_fetch_array( $data );

        if ( $rowF != NULL ) {

          $id_solicitud =         $rowF['id_solicitud'];
          $cmbLotes =             $rowF['id_lote'];
          $cmbValijas =           $rowF['id_valija'];
          $fecha_solicitud_del =  $rowF['fecha_solicitud_del'];
          $fecha_modificacion =   $rowF['fecha_modificacion'];
          $cmbtipomovimiento =    $rowF['id_movimiento'];
          $cmbDelegaciones =      $rowF['delegacion'];
          $cmbSubdelegaciones =   $rowF['subdelegacion'];
          $primer_apellido =      $rowF['primer_apellido'];
          $segundo_apellido =     $rowF['segundo_apellido'];
          $nombre =               $rowF['nombre'];
          $matricula =            $rowF['matricula'];
          $curp =                 $rowF['curp'];
          $usuario =              $rowF['usuario'];
          $cmbgponuevo =          $rowF['id_grupo_nuevo'];
          $cmbgpoactual =         $rowF['id_grupo_actual'];
          $cmbcausarechazo =      $rowF['id_causarechazo'];
          $comentario =           $rowF['comentario'];
          $archivo =              $rowF['archivo'];
          $creada_por =           $rowF['creada_por'];

          /*$new_file =             $_FILES['new_file']['name'];
          $new_file_type = $_FILES['new_file']['type'];
          $new_file_size = $_FILES['new_file']['size'];*/

        }
        else {
          ?>
          
          <?php
            echo '<p class="error">Hubo un problema leyendo la información de la solicitud. Sólo se pueden editar solicitudes sin lote.</p>';
          ?>
          
          <?php
            require_once('lib/footer.php');
            exit();
        }
    ?>
        <div class="contenedor">
          <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'] . '?id_solicitud=' . $id_solicitud; ?>">
            <h2>Datos de la solicitud localizada</h2>
            <ul>

              <li>
                <input hidden class="textinput" type="text" required name="id_solicitud" id="id_solicitud" value="<?php if ( !empty( $id_solicitud ) ) echo $id_solicitud; ?>"/>
              </li>

              <li>
                <label for="cmbValijas">Número de Valija/Oficio</label>
                <select class="textinput" id="cmbValijas" name="cmbValijas">
                  <option value="0">Seleccione # de Valija/Oficio</option>
                  <?php
                    $query = "SELECT ctas_valijas.id_valija, 
                                ctas_valijas.delegacion AS num_del, 
                                dspa_delegaciones.descripcion AS delegacion_descripcion, 
                                ctas_valijas.num_oficio_del,
                                ctas_valijas.num_oficio_ca, 
                                ctas_valijas.id_user
                              FROM ctas_valijas, dspa_delegaciones 
                              WHERE ctas_valijas.delegacion = dspa_delegaciones.delegacion
                              ORDER BY ctas_valijas.fecha_recepcion_ca DESC, ctas_valijas.id_valija";

                    $result = mysqli_query( $dbc, $query );

                    while ( $row = mysqli_fetch_array( $result ) ) {
                      if ( $cmbValijas == $row['id_valija'] ) 
                          $textselected = 'selected';
                        else
                          $textselected = '';
                        echo '<option value="' . $row['id_valija'] . '" ' . $textselected . '>' . $row['num_oficio_ca'] . ': ' . $row['num_del'] . '-' . $row['delegacion_descripcion'] . '</option>';
                    }
                  ?>
                </select>
              </li>

              <li>
                <label for="fecha_solicitud_del">Fecha solicitud:</label>
                <input class="textinputsmall" type="date" id="fecha_solicitud_del" name="fecha_solicitud_del" value="<?php if (!empty($fecha_solicitud_del)) echo $fecha_solicitud_del; ?>" />
              </li>

              <li>
                <label for="cmbtipomovimiento">Tipo de Movimiento</label>
                <select id="cmbtipomovimiento" name="cmbtipomovimiento">
                  <option value="0">Seleccione Tipo de Movimiento</option>
                  <?php
                    $query = "SELECT * 
                              FROM ctas_movimientos 
                              ORDER BY 1 ASC";
                    $result = mysqli_query( $dbc, $query );
                    while ( $row = mysqli_fetch_array( $result ) ) {
                      if ( $cmbtipomovimiento == $row['id_movimiento'] ) 
                        $textselected = 'selected';
                      else
                        $textselected = '';
                      
                      echo '<option value="' . $row['id_movimiento'] . '" ' . $textselected . '>' . $row['descripcion'] . '</option>';
                    }
                  ?>
                </select>
              </li>

              <li>
                <label for="cmbDelegaciones">Delegación IMSS</label>
                <select class="textinput" id="cmbDelegaciones" name="cmbDelegaciones">
                  <option value="0">Seleccione Delegación</option>
                  <?php
                    $query = "SELECT * 
                              FROM dspa_delegaciones 
                              WHERE activo = 1 
                              ORDER BY delegacion";
                    $result = mysqli_query( $dbc, $query );
                    while ( $row = mysqli_fetch_array( $result ) ) {
                      if ( $cmbDelegaciones == $row['delegacion'] )
                          $textselected = 'selected';
                        else
                          $textselected = '';  

                        echo '<option value="' . $row['delegacion'] . '" ' . $textselected . '>' . $row['delegacion'] . ' - ' . $row['descripcion'] . '</option>';
                      }
                  ?>
                </select>
              </li>

              <li>
                <label for="cmbSubdelegaciones">Subdelegación IMSS</label>
                <select id="cmbSubdelegaciones" name="cmbSubdelegaciones">
                  <option value="-1">Seleccione Subdelegación</option>
                  <?php
                      if ( !empty( $cmbSubdelegaciones ) || $cmbSubdelegaciones == "0" ) {
                        $query = "SELECT * 
                                  FROM dspa_subdelegaciones 
                                  WHERE delegacion = " . $cmbDelegaciones . " ORDER BY subdelegacion";
                        $result = mysqli_query( $dbc, $query );
                      }
                      while ( $row = mysqli_fetch_array( $result ) )  {
                        if ( $cmbSubdelegaciones == $row['subdelegacion'] )
                          $textselected = 'selected';
                        else
                          $textselected = '';  

                        echo '<option value="' . $row['subdelegacion'] . '" ' . $textselected . '>' . $row['subdelegacion'] . ' - ' . $row['descripcion'] . '</option>';
                      }
                    ?>
                  </select>
              </li>

              <li>
                <label for="primer_apellido">Primer apellido</label>
                <input class="textinput" type="text" required name="primer_apellido" id="primer_apellido" maxlength="32" placeholder="Escriba el primer apellido" value="<?php if ( !empty( $primer_apellido ) ) echo $primer_apellido; ?>"/>
              </li>

              <li>
                <label for="segundo_apellido">Segundo apellido</label>
                <input class="textinput" type="text" name="segundo_apellido" id="segundo_apellido" maxlength="32" placeholder="Escriba el segundo apellido" value="<?php if ( !empty( $segundo_apellido ) ) echo $segundo_apellido; ?>"/>
              </li>

              <li>
                <label for="nombre">Nombre(s)</label>
                <input class="textinput" type="text" required name="nombre" id="nombre" maxlength="32" placeholder="Escriba el nombre(s)" value="<?php if ( !empty( $nombre ) ) echo $nombre; ?>"/>
              </li>

              <li>
                <label for="matricula">Matrícula</label>
                <input class="textinput" type="text" required name="matricula" id="matricula" maxlength="10" placeholder="Escriba la matrícula" value='<?php if ( !empty( $matricula ) ) echo $matricula; ?>'/>
              </li>

              <li>
                <label for="curp">CURP (Usuario)</label>
                <input class="textinput" type="text" required name="curp" id="curp" maxlength="18" placeholder="Escriba su CURP" value="<?php if ( !empty( $curp ) ) echo $curp; ?>" />
              </li>

              <li>
                <label for="usuario">Usuario</label>
                <input class="textinput" type="text" required name="usuario" id="usuario" maxlength="7" placeholder="Escriba el usuario" value="<?php if ( !empty( $usuario ) ) echo $usuario; ?>" />
              </li>

              <li>
                <label for="cmbgpoactual">Grupo Actual</label>
                <select id="cmbgpoactual" name="cmbgpoactual">
                    <option value="0">Seleccione Grupo Actual</option>
                    <?php
                      $query = "SELECT * 
                                FROM ctas_grupos 
                                WHERE id_grupo <> 0
                                ORDER BY descripcion ASC";
                      $result = mysqli_query( $dbc, $query );
                      while ( $row = mysqli_fetch_array( $result ) ) {
                        if ( $cmbgpoactual == $row['id_grupo'] )
                          $textselected = 'selected';
                        else
                          $textselected = '';  

                        echo '<option value="' . $row['id_grupo'] . '" ' . $textselected .'>' . $row['descripcion'] . '</option>';
                      }
                    ?>
                  </select>
              </li>

              <li>
                <label for="cmbgponuevo">Grupo Nuevo</label>
                <select id="cmbgponuevo" name="cmbgponuevo">
                    <option value="0">Seleccione Grupo Nuevo</option>
                    <?php
                      $query = "SELECT * 
                                FROM ctas_grupos 
                                WHERE id_grupo <> 0
                                ORDER BY descripcion ASC";
                      $result = mysqli_query( $dbc, $query );
                      while ( $row = mysqli_fetch_array( $result ) ) {
                        if ( $cmbgponuevo == $row['id_grupo'] )
                          $textselected = 'selected';
                        else
                          $textselected = '';  

                        echo '<option value="' . $row['id_grupo'] . '" ' . $textselected .'>' . $row['descripcion'] . '</option>';
                      }
                    ?>
                  </select>
              </li>

              <li>
                <label for="cmbcausarechazo">Causa de Rechazo</label>
                <select class="combo0" id="cmbcausarechazo" name="cmbcausarechazo">
                    <option value="-1">Seleccione Causa de Rechazo</option>
                    <?php
                      $query = "SELECT * 
                                FROM ctas_causasrechazo
                                WHERE id_causarechazo <> -1
                                ORDER BY id_causarechazo ASC";
                      $result = mysqli_query( $dbc, $query );
                      while ( $row = mysqli_fetch_array( $result ) ){
                        if ( $cmbcausarechazo == $row['id_causarechazo'] )
                          $textselected = 'selected';
                        else
                          $textselected = '';  

                        echo '<option value="' . $row['id_causarechazo'] . '" ' . $textselected .'>' . $row['id_causarechazo'] . ' - ' . $row['descripcion'] . '</option>';
                      }
                    ?>
                </select>
              </li>

              <li>
                <label for="comentario">Comentario</label>
                <textarea class="textinput" id="comentario" name="comentario" maxlength="256" placeholder="Escriba comentarios (opcional)"><?php if ( !empty( $comentario ) ) echo $comentario; ?></textarea>
              </li>

              <li>
                <label for="old_file">Archivo Actual</label>
                <?php 
                  if ( !empty( $archivo ) ) 
                    echo '<a href="' . MM_UPLOADPATH_CTASSINDO . '\\' . $archivo . '"  target="_new">' . $archivo . '</a>';
                  else echo '(Vacío)';
                ?>
              </li>

              <li>
                <label for="new_file">Archivo Nuevo</label>
                <input type="file" id="new_file" name="new_file">
              </li>

              <li>
                <label for="id_user">Modificada/Capturada por:</label>
                <input disabled class="textinput" type="text" name="id_user" id="id_user" value="<?php if ( !empty( $creada_por ) ) echo $creada_por; ?>"/>
              </li>

              <li>
                <label for="fecha_modificacion">Fecha de modificación:</label>
                <input disabled class="text" type="text" name="fecha_modificacion" id="fecha_modificacion" value="<?php if ( !empty( $fecha_modificacion ) ) echo $fecha_modificacion; ?>"/>
              </li>

              <br/>

              <li class="buttons">
                <input type="submit" name="submit" value="Actualiza Solicitud">
                <input type="reset" name="reset" value="Reset">
              </li>
              
            </ul>
          </form>
        </div>

<!--         }Fin de: if ( mysqli_num_rows( $data ) == 1 ) -->

    <?php
      }
    ?>
<!--       else {

      } -->

  <?php
    //mysqli_close( $dbc );
    // Insert the page footer
    require_once('lib/footer.php');
  ?>
