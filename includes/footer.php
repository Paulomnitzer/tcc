    </div> <!-- Fim do container principal -->

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">Sistema desenvolvido pelo Grupo 3 da turma EUI para o TCC.</p>
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
    
    <!-- Mensagens via Modal (substitui alerts) -->
    <!-- Modal genérico para exibir mensagens de sucesso/erro/aviso -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Mensagem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
                    <!-- Conteúdo dinâmico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Preparar dados de mensagem a partir de variáveis locais ou sessão
    $modal_type = null; // 'success'|'danger'|'warning'|'info'
    $modal_content = '';

    // Prioridade: variáveis locais $sucesso / $erro, depois $_SESSION
    if (isset($sucesso) && !empty($sucesso)) {
        $modal_type = 'success';
        $modal_content = is_array($sucesso) ? implode("<br>", $sucesso) : $sucesso;
    } elseif (isset($erro) && !empty($erro)) {
        $modal_type = 'danger';
        $modal_content = is_array($erro) ? '<ul><li>' . implode('</li><li>', $erro) . '</li></ul>' : $erro;
    } elseif (!empty($_SESSION['sucesso'])) {
        $modal_type = 'success';
        $modal_content = is_array($_SESSION['sucesso']) ? implode("<br>", $_SESSION['sucesso']) : $_SESSION['sucesso'];
        unset($_SESSION['sucesso']);
    } elseif (!empty($_SESSION['erros'])) {
        $modal_type = 'danger';
        $erros_arr = $_SESSION['erros'];
        if (is_array($erros_arr)) {
            $modal_content = '<ul><li>' . implode('</li><li>', $erros_arr) . '</li></ul>';
        } else {
            $modal_content = $erros_arr;
        }
        unset($_SESSION['erros']);
    } elseif (!empty($_SESSION['mensagem'])) {
        $modal_type = $_SESSION['mensagem_tipo'] ?? 'info';
        $modal_content = $_SESSION['mensagem'];
        unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']);
    }
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remover qualquer alert visível na página e agregar seu conteúdo
        const alerts = Array.from(document.querySelectorAll('.alert'));
        let aggregated = '';
        let inferredType = null;

        alerts.forEach(a => {
            // Inferir tipo a partir das classes
            if (!inferredType) {
                if (a.classList.contains('alert-success')) inferredType = 'success';
                else if (a.classList.contains('alert-danger')) inferredType = 'danger';
                else if (a.classList.contains('alert-warning')) inferredType = 'warning';
                else inferredType = 'info';
            }
            aggregated += a.innerHTML + '<br>';
            a.remove();
        });

        // Se havia alerts DOM, mostrar no modal
        if (aggregated) {
            const modalEl = document.getElementById('messageModal');
            document.getElementById('messageModalBody').innerHTML = aggregated;
            const label = document.getElementById('messageModalLabel');
            label.textContent = inferredType === 'success' ? 'Sucesso' : (inferredType === 'danger' ? 'Erro' : 'Aviso');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            return;
        }

        // Caso não tenha alerts DOM, verificar se o servidor injetou conteúdo para modal
        <?php if (!empty($modal_content)): ?>
            (function(){
                const modalEl = document.getElementById('messageModal');
                const body = document.getElementById('messageModalBody');
                body.innerHTML = <?php echo json_encode($modal_content); ?>;
                const label = document.getElementById('messageModalLabel');
                const type = <?php echo json_encode($modal_type ?? 'info'); ?>;
                label.textContent = type === 'success' ? 'Sucesso' : (type === 'danger' ? 'Erro' : 'Aviso');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            })();
        <?php endif; ?>
    });
    </script>
</body>
</html>
