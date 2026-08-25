<?php
namespace App;

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DeporteController;
use App\Controllers\TorneoController;
use App\Controllers\EquipoController;
use App\Controllers\JugadorController;
use App\Controllers\JornadaController;
use App\Controllers\PartidoController;
use App\Controllers\ResultadoController;
use App\Controllers\ClasificacionController;
use App\Controllers\AuditoriaController;
use App\Controllers\GrupoController;
use App\Controllers\FixtureController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RbacMiddleware;
use App\Middleware\RateLimitMiddleware;

class Routes
{
    public static function register(Router $r): void
    {
        // Auth & Usuarios
        $r->post('/api/v1/auth/login', [AuthController::class,'login']);
        $r->post('/api/v1/auth/refresh', [AuthController::class,'refresh']);
        $r->post('/api/v1/auth/logout', [AuthController::class,'logout'], [AuthMiddleware::class]);
        $r->get('/api/v1/usuarios', [\App\Controllers\UsuarioController::class,'index'], [AuthMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/usuarios', [\App\Controllers\UsuarioController::class,'store'], [AuthMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/usuarios/{id}', [\App\Controllers\UsuarioController::class,'destroy'], [AuthMiddleware::class, RbacMiddleware::class]);

        // Deportes (GET público, escritura admin)
        $r->get('/api/v1/deportes', [DeporteController::class,'index']);
        $r->post('/api/v1/deportes', [DeporteController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/deportes/{id}', [DeporteController::class,'show']);
        $r->put('/api/v1/deportes/{id}', [DeporteController::class,'update'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/deportes/{id}', [DeporteController::class,'destroy'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);

        // Torneos
        $r->get('/api/v1/torneos', [TorneoController::class,'index']);
        $r->post('/api/v1/torneos', [TorneoController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/torneos/{id}', [TorneoController::class,'show']);
        $r->put('/api/v1/torneos/{id}', [TorneoController::class,'update'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/torneos/{id}', [TorneoController::class,'destroy'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/torneos/{id}/equipos', [TorneoController::class,'attachEquipo'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/torneos/{id}/equipos/{equipoId}', [TorneoController::class,'detachEquipo'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/torneos/{id}/editores', [TorneoController::class,'attachEditor'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/torneos/{id}/editores/{usuarioId}', [TorneoController::class,'detachEditor'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/mis-torneos', [TorneoController::class,'misTorneos'], [AuthMiddleware::class]);
        $r->get('/api/v1/torneos/{id}/equipos', [EquipoController::class,'porTorneo']);

        // Equipos
        $r->get('/api/v1/equipos', [EquipoController::class,'index']);
        $r->post('/api/v1/equipos', [EquipoController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/equipos/{id}', [EquipoController::class,'show']);
        $r->put('/api/v1/equipos/{id}', [EquipoController::class,'update'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/equipos/{id}', [EquipoController::class,'destroy'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/equipos/{id}/jugadores', [JugadorController::class,'index']);
        $r->post('/api/v1/equipos/{id}/jugadores', [JugadorController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->put('/api/v1/jugadores/{id}', [JugadorController::class,'update'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/jugadores/{id}', [JugadorController::class,'destroy'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);

        // Grupos
        $r->get('/api/v1/torneos/{id}/grupos', [GrupoController::class,'index']);
        $r->post('/api/v1/torneos/{id}/grupos', [GrupoController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/torneos/{id}/grupos/auto', [GrupoController::class,'auto'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/grupos/{id}', [GrupoController::class,'destroy'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/grupos/{id}/equipos', [GrupoController::class,'addEquipo'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/grupos/{id}/equipos/{equipoId}', [GrupoController::class,'removeEquipo'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->put('/api/v1/grupos/reagrupar', [GrupoController::class,'reagrupar'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/torneos/{id}/fixture/generar', [FixtureController::class,'generar'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/torneos/{id}/fixture/eliminatoria', [FixtureController::class,'eliminatoria'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/torneos/{id}/partidos/sin-asignar', [FixtureController::class,'sinAsignar']);

        // Jornadas
        $r->get('/api/v1/torneos/{id}/jornadas', [JornadaController::class,'index']);
        $r->post('/api/v1/torneos/{id}/jornadas', [JornadaController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/jornadas/{id}', [JornadaController::class,'show']);
        $r->put('/api/v1/jornadas/{id}', [JornadaController::class,'update'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/jornadas/{id}', [JornadaController::class,'destroy'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/torneos/{id}/calendario', [JornadaController::class,'calendario']);

        // Partidos
        $r->get('/api/v1/jornadas/{id}/partidos', [PartidoController::class,'porJornada']);
        $r->post('/api/v1/jornadas/{id}/partidos', [PartidoController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->get('/api/v1/partidos/{id}', [PartidoController::class,'show']);
        $r->put('/api/v1/partidos/{id}', [PartidoController::class,'update'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->delete('/api/v1/partidos/{id}', [PartidoController::class,'destroy'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);

        // Resultados
        $r->post('/api/v1/partidos/{id}/resultados', [ResultadoController::class,'store'], [AuthMiddleware::class, RateLimitMiddleware::class]);
        $r->get('/api/v1/partidos/{id}/resultados', [ResultadoController::class,'show'], [AuthMiddleware::class]);
        $r->put('/api/v1/partidos/{id}/resultados', [ResultadoController::class,'update'], [AuthMiddleware::class, RateLimitMiddleware::class]);
        $r->post('/api/v1/partidos/{id}/resultados/aprobar', [ResultadoController::class,'aprobar'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);
        $r->post('/api/v1/partidos/{id}/resultados/rechazar', [ResultadoController::class,'rechazar'], [AuthMiddleware::class, RateLimitMiddleware::class, RbacMiddleware::class]);

        // Clasificaciones / estadísticas (público)
        $r->get('/api/v1/torneos/{id}/clasificaciones', [ClasificacionController::class,'tabla']);
        $r->get('/api/v1/torneos/{id}/equipos/{equipoId}/estadisticas', [ClasificacionController::class,'estadisticasEquipo']);
        $r->get('/api/v1/torneos/{id}/jugadores/{jugadorId}/estadisticas', [ClasificacionController::class,'estadisticasJugador']);

        // Auditoría (solo admin)
        $r->get('/api/v1/auditoria', [AuditoriaController::class,'index'], [AuthMiddleware::class, RbacMiddleware::class]);
    }
}
