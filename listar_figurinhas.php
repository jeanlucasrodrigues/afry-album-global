<?php
header('Content-Type: application/json');

// ==========================================
// CONFIGURAÇÕES DO BANCO GLOBAL
// ==========================================
$servidor = "localhost"; 
$usuario = "trabu321_afry-global"; // <<< Seu novo usuário global criado no cPanel
$senha = "kqeF[.z6iU}S"; // <<< A senha que você salvou no bloco de notas
$banco = "trabu321_album_global"; // <<< O novo banco de dados global

$conn = new mysqli($servidor, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die(json_encode([]));
}

// 📐 ATUALIZADO: Agora puxamos a imagem e também o país da figurinha
$sql = "SELECT imagem, pais FROM envios_album ORDER BY id DESC";
$result = $conn->query($sql);

$figurinhas = [];
while ($row = $result->fetch_assoc()) {
    $figurinhas[] = $row;
}

echo json_encode($figurinhas);
$conn->close();
?>