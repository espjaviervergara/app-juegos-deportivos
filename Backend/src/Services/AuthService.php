<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Jwt;
use App\Repositories\UsuarioRepository;

class AuthService
{
    private array $cfg;
    private UsuarioRepository $users;

    public function __construct(array $config=null)
    {
        $this->cfg = $config ?? require __DIR__ . '/../../config/app.php';
        $this->users = new UsuarioRepository();
    }

    public function login(string $email, string $password): ?array
    {
        $u = $this->users->findByEmail($email);
        if (!$u || !password_verify($password, $u['password_hash'])) return null;
        if (!$u['activo']) return null;
        return $this->issueTokens($u);
    }

    public function issueTokens(array $user): array
    {
        $now = time();
        $access = Jwt::encode([
            'sub'=>$user['id'],'email'=>$user['email'],'rol'=>$user['rol'],
            'iat'=>$now,'exp'=>$now + $this->cfg['jwt']['access_ttl'],'iss'=>$this->cfg['jwt']['issuer']
        ], $this->cfg['jwt']['secret']);
        $rawRefresh = bin2hex(random_bytes(32));
        $hash = hash('sha256', $rawRefresh);
        $exp = date('Y-m-d H:i:s', $now + $this->cfg['jwt']['refresh_ttl']);
        $pdo = Database::pdo($this->cfg);
        $pdo->prepare("INSERT INTO refresh_tokens (usuario_id, token_hash, expires_at) VALUES (?,?,?)")
            ->execute([$user['id'],$hash,$exp]);
        return ['accessToken'=>$access,'expiresIn'=>$this->cfg['jwt']['access_ttl'],'refreshToken'=>$rawRefresh,'user'=>$user];
    }

    public function refresh(string $raw): ?array
    {
        $hash = hash('sha256', $raw);
        $pdo = Database::pdo($this->cfg);
        $stmt = $pdo->prepare("SELECT * FROM refresh_tokens WHERE token_hash=? AND revoked=0 AND expires_at > NOW()");
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        if (!$row) return null;
        // revoke old
        $pdo->prepare("UPDATE refresh_tokens SET revoked=1 WHERE id=?")->execute([$row['id']]);
        $user = (new UsuarioRepository())->find($row['usuario_id']);
        if (!$user) return null;
        $tokens = $this->issueTokens($user);
        // link rotation
        $pdo->prepare("UPDATE refresh_tokens SET rotated_to=? WHERE id=?")->execute([$pdo->lastInsertId(), $row['id']]);
        return $tokens;
    }

    public function logout(string $raw): void
    {
        $hash = hash('sha256', $raw);
        Database::pdo($this->cfg)->prepare("UPDATE refresh_tokens SET revoked=1 WHERE token_hash=?")->execute([$hash]);
    }

    public function verify(string $token): ?array
    {
        return Jwt::decode($token, $this->cfg['jwt']['secret']);
    }
}
