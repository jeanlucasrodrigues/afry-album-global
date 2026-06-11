<?php

// DIAGNOSTIC LINES (Remove after it works perfectly)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// ==========================================
// 1. ACCESS AND DATABASE CONFIGURATIONS
// ==========================================
$senha_definida = "AfrySticker2026!#@"; // << NEW STRONG PASSWORD FOR GLOBAL PANEL

$servidor = "localhost";
$usuario = "trabu321_afry-global"; // Fixed with your hyphenated user
$senha_banco = "kqeF[.z6iU}S"; // << Put your global database password here
$banco = "trabu321_album_global"; 

// Logout Logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Login Logic
$erro = "";
if (isset($_POST['senha'])) {
    if ($_POST['senha'] === $senha_definida) {
        $_SESSION['logado'] = true;
    } else {
        $erro = "Incorrect password! Please try again.";
    }
}

// If not logged in, show the login screen
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AFRY Panel</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; display: flex; height: 100vh; align-items: center; justify-content: center; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 360px; text-align: center; }
        h2 { color: #115c40; margin-bottom: 20px; }
        input[type="password"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 1rem; }
        button { width: 100%; padding: 12px; background: #115c40; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem; }
        button:hover { background: #0d4631; }
        .erro { color: #ef4444; font-size: 0.9rem; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>AFRY Album Panel</h2>
        <?php if($erro) echo "<div class='erro'>$erro</div>"; ?>
        <form method="POST">
            <input type="password" name="senha" placeholder="Enter access password" required>
            <button type="submit">Access Panel</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// Connected to MySQL
$conn = new mysqli($servidor, $usuario, $senha_banco, $banco);
if ($conn->connect_error) { die("Connection error."); }

// Secure Deletion Logic
if (isset($_POST['acao']) && $_POST['acao'] === 'deletar' && isset($_POST['id']) && isset($_POST['senha_confirmacao'])) {
    if ($_POST['senha_confirmacao'] === $senha_definida) {
        $id_deletar = intval($_POST['id']);
        
        $stmt_busca = $conn->prepare("SELECT imagem FROM envios_album WHERE id = ?");
        $stmt_busca->bind_param("i", $id_deletar);
        $stmt_busca->execute();
        $res_busca = $stmt_busca->get_result();
        
        if ($row_busca = $res_busca->fetch_assoc()) {
            $arquivo_fisico = "arquivos_enviados/" . $row_busca['imagem'];
            if (file_exists($arquivo_fisico)) {
                unlink($arquivo_fisico); 
            }
        }
        $stmt_busca->close();

        $stmt_del = $conn->prepare("DELETE FROM envios_album WHERE id = ?");
        $stmt_del->bind_param("i", $id_deletar);
        $stmt_del->execute();
        $stmt_del->close();
        
        header("Location: admin.php");
        exit;
    } else {
        echo "<script>alert('Incorrect confirmation password! The sticker was not deleted.'); window.location.href='admin.php';</script>";
        exit;
    }
}

$sql = "SELECT * FROM envios_album ORDER BY data_envio DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel - AFRY Album</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 30px; color: #334155; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 20px; }
        h1 { color: #115c40; margin: 0; font-size: 1.8rem; }
        .btn-logout { background: #64748b; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 0.9rem; }
        .btn-logout:hover { background: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 14px; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:hover { background: #f8fafc; }
        .thumb { width: 70px; height: 90px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; display: block; }
        
        .actions-cell {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-view { 
            background: #115c40; 
            color: white; 
            text-decoration: none; 
            padding: 8px 14px; 
            border-radius: 6px; 
            font-size: 0.85rem; 
            font-weight: bold; 
            display: inline-block;
            transition: all 0.2s ease;
        }
        .btn-view:hover { 
            background: #0d4631; 
            transform: translateY(-1px);
        }

        .btn-delete { 
            background: #ef4444; 
            color: white; 
            border: none; 
            padding: 8px 14px; 
            border-radius: 6px; 
            font-size: 0.85rem; 
            font-weight: bold; 
            cursor: pointer; 
            display: inline-block;
            transition: all 0.2s ease;
        }
        .btn-delete:hover { 
            background: #dc2626; 
            transform: translateY(-1px);
        }

        .contador { font-size: 1.1rem; font-weight: 600; color: #475569; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>AFRY Corporate Album Dashboard</h1>
        <a href="admin.php?logout=true" class="btn-logout">Log Out</a>
    </div>

    <div class="contador">
         Total Stickers Submitted: <?php echo $resultado->num_rows; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Country</th>
                <th>Submission Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($resultado->num_rows > 0) {
                while($row = $resultado->fetch_assoc()) {
                    $data_formatada = date('d/m/Y H:i', strtotime($row['data_envio']));
                    echo "<tr>";
                    echo "<td><img src='arquivos_enviados/" . $row['imagem'] . "' class='thumb'></td>";
                    echo "<td><strong>" . htmlspecialchars($row['nome']) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['pais']) . "</td>"; // Added Country dynamic display
                    echo "<td>" . $data_formatada . "</td>";
                    
                    echo "<td>";
                    echo "<div class='actions-cell'>";
                    echo "<a href='arquivos_enviados/" . $row['imagem'] . "' target='_blank' class='btn-view'>View Full Photo</a>";
                    
                    echo "<form method='POST' style='display:inline;' onsubmit='return solicitarSenhaExclusao(this);'>";
                    echo "<input type='hidden' name='acao' value='deletar'>";
                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                    echo "<input type='hidden' name='senha_confirmacao' value=''>";
                    echo "<button type='submit' class='btn-delete'>Delete</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</td>";
                    
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center; color:#94a3b8; padding:30px;'>No stickers submitted yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function solicitarSenhaExclusao(formulario) {
    const senhaDigitada = prompt("SECURITY CONFIGURATION:\nTo permanently delete this sticker from the database and server, enter the master panel password:");
    
    if (senhaDigitada === null || senhaDigitada.trim() === "") {
        return false; 
    }
    
    formulario.querySelector('input[name="senha_confirmacao"]').value = senhaDigitada;
    return true;
}
</script>

</body>
</html>
<?php $conn->close(); ?>