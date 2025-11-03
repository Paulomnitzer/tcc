
<?php
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$page_title = 'Cadastro de Produto';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = sanitizar($_POST['nome'] ?? '');
    $preco = $_POST['preco'] ?? '';
    $descricao = sanitizar($_POST['descricao'] ?? '');
    $categoria = sanitizar($_POST['categoria'] ?? '');
    $estoque = $_POST['estoque'] ?? '';
    $limite_min = $_POST['limite_min'] ?? '';
    $imagem = '';

    $erros = [];

    if (empty($nome) || empty($estoque) || empty($limite_min) || empty($categoria)) {
        $erros[] = 'Preencha todos os campos obrigatórios.';
    }

    if (!empty($preco) && !is_numeric($preco)) {
        $erros[] = 'Preço deve ser numérico.';
    }

    if (!is_numeric($estoque)) {
        $erros[] = 'Estoque deve ser numérico.';
    }

    if (!is_numeric($limite_min)) {
        $erros[] = 'Limite mínimo deve ser numérico.';
    }

    // Upload da imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['imagem'];
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $permitidas)) {
            $erros[] = 'Apenas imagens JPG, JPEG, PNG, GIF e WEBP são permitidas.';
        }

        if ($arquivo['size'] > 1048576) {
            $erros[] = 'A imagem deve ter no máximo 1MB.';
        }

        if (empty($erros)) {
            $dir = '../../imgs/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $nomeArquivo = uniqid() . '_' . time() . '.' . $ext;
            $destino = $dir . $nomeArquivo;

            if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                $imagem = $nomeArquivo;
            } else {
                $erros[] = 'Erro ao fazer upload da imagem.';
            }
        }
    } elseif (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
        $erros[] = 'Erro no upload da imagem: ' . obterMensagemErroUpload($_FILES['imagem']['error']);
    }

    // Verificar produto duplicado
    if (empty($erros)) {
        $stmt = $conn->prepare('SELECT id FROM produto WHERE nome = ?');
        $stmt->bind_param('s', $nome);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $erros[] = 'Já existe um produto com este nome.';
        }
        $stmt->close();
    }

    // Inserir produto
    if (empty($erros)) {
        $stmt = $conn->prepare('INSERT INTO produto (nome, preco, descricao, categoria, estoque, limite_min, imagem) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sdssiis', $nome, $preco, $descricao, $categoria, $estoque, $limite_min, $imagem);

        if ($stmt->execute()) {
            $sucesso = 'Produto cadastrado com sucesso!';
            $nome = $preco = $descricao = $categoria = $estoque = $limite_min = $imagem = '';
        } else {
            $erros[] = 'Erro ao cadastrar produto: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Função para obter mensagem de erro do upload
function obterMensagemErroUpload($codigo) {
    switch ($codigo) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Arquivo muito grande.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload parcialmente feito.';
        case UPLOAD_ERR_NO_FILE:
            return 'Nenhum arquivo enviado.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Pasta temporária não encontrada.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Erro ao escrever no disco.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload interrompido por extensão.';
        default:
            return 'Erro desconhecido.';
    }
}

// Incluir header
include '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4><i class="fas fa-box me-2"></i>Cadastro de Produto</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Exibir mensagens de erro/sucesso -->
                    <?php if (!empty($erros)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?php echo htmlspecialchars($erro); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($sucesso)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($sucesso); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulário de cadastro -->
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome do Produto:</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preco" class="form-label">Preço (R$):</label>
                                <input type="number" step="0.01" class="form-control" id="preco" name="preco"
                                       value="<?php echo htmlspecialchars($preco ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="descricao" class="form-label">Descrição:</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                                          placeholder="Descrição do produto"><?php echo htmlspecialchars($descricao ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="descricao" class="form-label">Categoria:</label>
                                <textarea class="form-control" id="categoria" name="categoria" rows="3" 
                                          placeholder="Descrição do produto"><?php echo htmlspecialchars($descricao ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estoque" class="form-label">Estoque:</label>
                                <input type="number" class="form-control" id="estoque" name="estoque" 
                                       value="<?php echo htmlspecialchars($estoque ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="limite_min" class="form-label">Limite Mínimo:</label>
                                <input type="number" class="form-control" id="limite_min" name="limite_min"
                                       value="<?php echo htmlspecialchars($limite_min ?? ''); ?>" required>
                                <div class="form-text">Estoque mínimo permitido</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="imagem" class="form-label">Imagem do Produto:</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" 
                                       accept="image/jpeg, image/png, image/gif, image/webp">
                                <div class="form-text">
                                    Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP. Tamanho máximo: 1MB
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Cadastrar Produto
                            </button>
                        </div>
                        <div class="d-grid">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard
                            </a>
                        </div>
                    </form>

                    <hr>
                    
                    <div class="text-center">
                        <p class="mb-0">
                            <a href="lista-produtos.php" class="text-decoration-none">
                                <i class="fas fa-list me-1"></i>Ver todos os produtos
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>