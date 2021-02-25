            <!-- Sticky Footer -->
            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright © TR network 2019</span>
                    </div>
                </div>
            </footer>

        </div><!-- /#wrapper -->

		<!-- Scroll to Top Button-->
		<a class="scroll-to-top rounded" href="#page-top">
		    <i class="fas fa-angle-up"></i>
		</a>

		<!-- Logout Modal-->
		<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		    <div class="modal-dialog" role="document">
		        <div class="modal-content">
		            <div class="modal-header">
		                <h5 class="modal-title" id="exampleModalLabel">¿Seguro que quieres Salir?</h5>
		                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
		                    <span aria-hidden="true">×</span>
		                </button>
		            </div>
		            <div class="modal-body">Seleccione "Salir" para cerrar tu sesión.</div>
		            <div class="modal-footer">
		                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
		                <a class="btn btn-primary" href="logout">Salir</a>
		            </div>
		        </div>
		    </div>
		</div>

        <!-- Cargamos el script de sb-admin -->
        <script src="<?php echo BASE_URL.'public/sb-admin/sb-admin.min.js' ?>"></script>

        <!-- Cargamos el Script que  estará presente en todos los elementos que proviene del layaut -->
		<script src="<?php echo $_layoutParams['route_js']; ?>back.js"></script>

		<!-- se pueden poner scirpts propios de js -->
		<?php if (isset($_layoutParams['js']) && count($_layoutParams['js'])):
		    foreach ($_layoutParams['js'] as $js):?>
		        <script src="<?php echo $js; ?>" type="text/javascript"></script>
		    <?php endforeach;
		endif; ?>

            <script>
                var root = '<?php echo BASE_URL . 'admin/'; ?>';
            </script>

    </body>
</html>
