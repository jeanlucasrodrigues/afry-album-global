<?php
// ==========================================
// 1. CONFIGURAÇÕES DO BANCO DE DADOS GLOBAL
// ==========================================
$servidor = "localhost"; 
$usuario = "trabu321_afry-global"; // Mantido exatamente com o seu hífen verificado
$senha = "kqeF[.z6iU}S"; // Insira aqui a senha do banco global
$banco = "trabu321_album_global";

// Conecta ao banco de dados
$conn = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica se deu erro na conexão
if ($conn->connect_error) {
    die(json_encode(["sucesso" => false, "mensagem" => "Database connection error."]));
}

// ==========================================
// 2. RECEBER E SALVAR OS DADOS (PREPARED STATEMENTS)
// ==========================================
// ATUALIZADO: Agora o sistema exige o envio do campo 'pais' vindo do formulário
if (isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['pais']) && isset($_FILES['imagem'])) {
    
    // Pegamos os dados limpos
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $pais = trim($_POST['pais']); // Capturando o novo dado internacional!
    
    // Configurações do arquivo de imagem
    $pasta_destino = "arquivos_enviados/";
    $nome_imagem = "afry_" . time() . "_" . rand(1000, 9999) . ".jpg";
    $caminho_completo = $pasta_destino . $nome_imagem;
    
    // Move o arquivo temporário para a pasta final
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_completo)) {
        
        // PREPARED STATEMENT: Atualizado para incluir a coluna 'pais' no INSERT
        $stmt = $conn->prepare("INSERT INTO envios_album (nome, email, pais, imagem) VALUES (?, ?, ?, ?)");
        
        // O "ssss" indica que passaremos 4 strings seguras: nome, email, pais e imagem
        $stmt->bind_param("ssss", $nome, $email, $pais, $nome_imagem);
        
        if ($stmt->execute()) {
            echo json_encode(["sucesso" => true, "mensagem" => "Sticker created and saved successfully!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Error saving data to the global database."]);
        }
        
        $stmt->close(); 
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Error uploading image to the server folder."]);
    }
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Incomplete request data."]);
}

$conn->close();
?>