<?php
namespace App\Core;

class Jwt
{
    public static function encode(array $payload, string $secret, string $algo='HS256'): string
    {
        $header = ['alg'=>$algo,'typ'=>'JWT'];
        $b64h = self::b64(json_encode($header));
        $b64p = self::b64(json_encode($payload));
        $sig = hash_hmac('sha256', "$b64h.$b64p", $secret, true);
        return "$b64h.$b64p.".self::b64($sig);
    }

    public static function decode(string $token, string $secret, string $algo='HS256'): ?array
    {
        $parts = explode('.', $token);
        if (count($parts)!==3) return null;
        [$b64h,$b64p,$b64s] = $parts;
        $sig = self::b64d($b64s);
        $exp = hash_hmac('sha256', "$b64h.$b64p", $secret, true);
        if (!hash_equals($exp, $sig)) return null;
        $payload = json_decode(self::b64d($b64p), true);
        if (!$payload) return null;
        if (isset($payload['exp']) && $payload['exp'] < time()) return null;
        return $payload;
    }

    private static function b64(string $d): string { return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); }
    private static function b64d(string $d): string { $r=strtr($d,'-_','+/'); return base64_decode(str_pad($r, strlen($r)%4?strlen($r)+4-strlen($r)%4:strlen($r),'=')); }
}
