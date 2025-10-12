    </div> <!-- Fim do container principal -->

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-graduation-cap me-2"></i><?php echo SITE_NAME; ?></h5>
                    <p class="mb-0">Sistema desenvolvido para Trabalho de Conclusão de Curso.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <small>
                            Versão <?php echo SITE_VERSION; ?> | 
                            © <?php echo date('Y'); ?> - Todos os direitos reservados
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (se necessário) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- JavaScript customizado -->
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
    
    <!-- JavaScript adicional específico da página (se definido) -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo SITE_URL . '/js/' . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts inline específicos da página (se definido) -->
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?php echo $inline_scripts; ?>
        </script>
    <?php endif; ?>
</body>
</html>
