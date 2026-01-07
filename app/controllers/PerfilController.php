<?php
require_once __DIR__ . '/../models/Usuario.php';

class PerfilController
{
    public static function index($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        $usuario = Usuario::findById($pdo, $idUsuario);

        // garante sessão atualizada (nome/email/avatar)
        $_SESSION['usuario']['nome'] = $usuario['nome'] ?? $_SESSION['usuario']['nome'];
        $_SESSION['usuario']['email'] = $usuario['email'] ?? ($_SESSION['usuario']['email'] ?? '');
        $_SESSION['usuario']['avatar'] = $usuario['avatar'] ?? null;

        $titulo = "Perfil • Finanças";
        $view = __DIR__ . '/../views/perfil/index_content.php';
        require __DIR__ . '/../views/layout.php';
    }

    public static function update($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirm = $_POST['nova_senha_confirm'] ?? '';

        if (!$nome || !$email) {
            $_SESSION['erro'] = "Preencha nome e e-mail.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['erro'] = "E-mail inválido.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        $usuarioAtual = Usuario::findById($pdo, $idUsuario);
        if (!$usuarioAtual) {
            $_SESSION['erro'] = "Usuário não encontrado.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        $mudouEmail = strtolower($email) !== strtolower($usuarioAtual['email'] ?? '');
        $querTrocarSenha = strlen(trim($novaSenha)) > 0;

        if (($mudouEmail || $querTrocarSenha) && !$senhaAtual) {
            $_SESSION['erro'] = "Informe sua senha atual para salvar alterações.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        if (($mudouEmail || $querTrocarSenha) && !password_verify($senhaAtual, $usuarioAtual['senha'])) {
            $_SESSION['erro'] = "Senha atual incorreta.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        if (Usuario::emailExistsExcept($pdo, $email, $idUsuario)) {
            $_SESSION['erro'] = "Este e-mail já está em uso.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        if ($querTrocarSenha) {
            if (strlen($novaSenha) < 6) {
                $_SESSION['erro'] = "A nova senha deve ter no mínimo 6 caracteres.";
                header("Location: /financas/public/?url=perfil");
                exit;
            }
            if ($novaSenha !== $confirm) {
                $_SESSION['erro'] = "As senhas não conferem.";
                header("Location: /financas/public/?url=perfil");
                exit;
            }
        }

        try {
            $pdo->beginTransaction();

            Usuario::updateProfile($pdo, $idUsuario, $nome, $email);

            if ($querTrocarSenha) {
                $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
                Usuario::updatePassword($pdo, $idUsuario, $hash);
            }

            $pdo->commit();

            $_SESSION['usuario']['nome'] = $nome;
            $_SESSION['usuario']['email'] = $email;

            $_SESSION['sucesso'] = "Perfil atualizado com sucesso.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao salvar. Tente novamente.";
        }

        header("Location: /financas/public/?url=perfil");
        exit;
    }

    public static function updateAvatar($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        if (empty($_FILES['avatar']['name'])) {
            $_SESSION['erro'] = "Selecione uma imagem.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        $file = $_FILES['avatar'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erro'] = "Erro no upload.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        $max = 3 * 1024 * 1024; // 3MB
        if ((int)$file['size'] > $max) {
            $_SESSION['erro'] = "Imagem muito grande (máx 3MB).";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        $mime = mime_content_type($file['tmp_name']);
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $permitidos, true)) {
            $_SESSION['erro'] = "Formato inválido. Use JPG, PNG ou WEBP.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');

        $dir = __DIR__ . '/../../public/uploads/avatars/' . $idUsuario;
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $usuario = Usuario::findById($pdo, $idUsuario);

        // remove antigo
        if (!empty($usuario['avatar'])) {
            $old = $dir . '/' . $usuario['avatar'];
            if (is_file($old)) @unlink($old);
        }

        $nomeFinal = 'avatar_' . time() . '.' . $ext;
        $dest = $dir . '/' . $nomeFinal;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $_SESSION['erro'] = "Não foi possível salvar a imagem.";
            header("Location: /financas/public/?url=perfil");
            exit;
        }

        Usuario::updateAvatar($pdo, $idUsuario, $nomeFinal);
        $_SESSION['usuario']['avatar'] = $nomeFinal;

        $_SESSION['sucesso'] = "Foto atualizada.";
        header("Location: /financas/public/?url=perfil");
        exit;
    }

    public static function deleteAvatar($pdo)
    {
        $idUsuario = (int)($_SESSION['usuario']['id'] ?? 0);
        if (!$idUsuario) {
            header("Location: /financas/public/?url=login");
            exit;
        }

        $dir = __DIR__ . '/../../public/uploads/avatars/' . $idUsuario;
        $usuario = Usuario::findById($pdo, $idUsuario);

        if (!empty($usuario['avatar'])) {
            $old = $dir . '/' . $usuario['avatar'];
            if (is_file($old)) @unlink($old);
        }

        Usuario::updateAvatar($pdo, $idUsuario, null);
        $_SESSION['usuario']['avatar'] = null;

        $_SESSION['sucesso'] = "Foto removida.";
        header("Location: /financas/public/?url=perfil");
        exit;
    }
}
