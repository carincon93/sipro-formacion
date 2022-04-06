<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\WebController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegionalController;
use App\Http\Controllers\CentroFormacionController;
use App\Http\Controllers\ProgramaFormacionController;
use App\Http\Controllers\RedConocimientoController;
use App\Http\Controllers\TematicaEstrategicaController;
use App\Http\Controllers\MesaTecnicaController;
use App\Http\Controllers\GrupoInvestigacionController;
use App\Http\Controllers\LineaInvestigacionController;
use App\Http\Controllers\SemilleroInvestigacionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ConvocatoriaController;
use App\Http\Controllers\IdiController;
use App\Http\Controllers\ArbolProyectoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AnalisisRiesgoController;
use App\Http\Controllers\EntidadAliadaController;
use App\Http\Controllers\MiembroEntidadAliadaController;
use App\Http\Controllers\MesaSectorialController;
use App\Http\Controllers\HelpDeskController;
use App\Http\Controllers\PdfController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/**
 * Trae los centros de formación
 */
Route::get('web-api/centros-formacion', [WebController::class, 'centrosFormacion'])->name('web-api.centros-formacion');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('manual-usuario/download', [ProyectoController::class, 'downloadManualUsuario'])->name('manual-usuario.download');

    Route::get('/', [WebController::class, 'dashboard'])->name('dashboard');

    // Notificaciones
    Route::get('notificaciones', [UserController::class, 'showAllNotifications'])->name('notificaciones.index');
    Route::post('notificaciones/marcar-leido', [UserController::class, 'markAsReadNotification'])->name('notificaciones.marcar-leido');

    // Redirecciona según el tipo de proyecto
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/editar', [ProyectoController::class, 'edit'])->name('convocatorias.proyectos.edit');

    //Exporta resumen proyecto PDF
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/pdf', [PdfController::class, 'generateProjectSumary'])->name('convocatorias.proyectos.pdf');

    // Reportar problemas
    Route::get('reportar-problemas/crear', [HelpDeskController::class, 'create'])->name('reportar-problemas.create');
    Route::post('reportar-problemas/reportar', [HelpDeskController::class, 'report'])->name('reportar-problemas.report');

    // Cambiar contraseña
    Route::put('/users/cambiar-password', [UserController::class, 'changePassword'])->name('users.change-password');
    Route::get('/users/cambiar-password', [UserController::class, 'showChangePasswordForm'])->name('users.change-password-form');

    // Muestra los participantes
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes', [ProyectoController::class, 'participantes'])->name('convocatorias.proyectos.participantes');
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/users', [ProyectoController::class, 'filterParticipantes'])->name('convocatorias.proyectos.participantes.users');
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/users/link', [ProyectoController::class, 'linkParticipante'])->name('convocatorias.proyectos.participantes.users.link');
    Route::put('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/users/link', [ProyectoController::class, 'updateParticipante'])->name('convocatorias.proyectos.participantes.users.update');
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/users/register', [ProyectoController::class, 'registerParticipante'])->name('convocatorias.proyectos.participantes.users.register');
    Route::delete('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/users/unlink', [ProyectoController::class, 'unlinkParticipante'])->name('convocatorias.proyectos.participantes.users.unlink');

    // Vincula y filtra los programas
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/programas-formacion', [ProyectoController::class, 'filterProgramasFormacion'])->name('convocatorias.proyectos.participantes.programas-formacion');
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/programas-formacion/link', [ProyectoController::class, 'linkProgramaFormacion'])->name('convocatorias.proyectos.participantes.programas-formacion.link');
    Route::delete('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/programas-formacion/unlink', [ProyectoController::class, 'unlinkProgramaFormacion'])->name('convocatorias.proyectos.participantes.programas-formacion.unlink');

    // Vincula y filtra los semilleros
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/semilleros-investigacion', [ProyectoController::class, 'filterSemillerosInvestigacion'])->name('convocatorias.proyectos.participantes.semilleros-investigacion');
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/semilleros-investigacion/link', [ProyectoController::class, 'linkSemilleroInvestigacion'])->name('convocatorias.proyectos.participantes.semilleros-investigacion.link');
    Route::delete('convocatorias/{convocatoria}/proyectos/{proyecto}/participantes/semilleros-investigacion/unlink', [ProyectoController::class, 'unlinkSemilleroInvestigacion'])->name('convocatorias.proyectos.participantes.semilleros-investigacion.unlink');

    Route::put('convocatorias/{convocatoria}/proyectos/{proyecto}/cadena-valor/propuesta-sostenibilidad', [ProyectoController::class, 'updatePropuestaSostenibilidad'])->name('convocatorias.proyectos.propuesta-sostenibilidad');
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/cadena-valor', [ProyectoController::class, 'showCadenaValor'])->name('convocatorias.proyectos.cadena-valor');

    // Trae las actividades por resultado
    Route::get('web-api/resultados/{resultado}/actividades', [WebController::class, 'resultadosActividades'])->name('web-api.resultados.actividades');

    /**
     * Programas de formación
     * 
     */
    Route::get('web-api/centros-formacion/{centro_formacion}/programas-formacion', [WebController::class, 'programasFormacion'])->name('web-api.programas-formacion');

    /**
     * Estados de sistema de gestión
     * 
     */
    Route::get('web-api/estados-sistema-gestion/{tipo_proyecto_st}', [WebController::class, 'estadosSistemaGestion'])->name('web-api.estados-sistema-gestion');

    /**
     * Programas de formación articulados
     * 
     */
    Route::get('web-api/programas-formacion-articulados', [WebController::class, 'programasFormacionArticulados'])->name('web-api.programas-formacion-articulados');

    /**
     * Regionales
     * 
     * Trae las regiones
     */
    Route::get('web-api/regiones', [WebController::class, 'regiones'])->name('web-api.regiones');

    /**
     * Trae las regionales
     */
    Route::get('web-api/regionales', [WebController::class, 'regionales'])->name('web-api.regionales');

    Route::resource('regionales', RegionalController::class)->parameters(['regionales' => 'regional'])->except(['show']);

    /**
     * Trae los centros de formación por regional
     */
    Route::get('web-api/regional/{regional}/centros-formacion', [WebController::class, 'centrosFormacionRegional'])->name('web-api.centros-formacion-ejecutor');

    /**
     * Centros de formación
     * 
     * Trae los subdirectores
     */
    Route::get('web-api/users/{rol}', [WebController::class, 'subdirectores'])->name('web-api.users');

    Route::resource('centros-formacion', CentroFormacionController::class)->except(['show'])->parameters(['centros-formacion' => 'centro-formacion']);

    /**
     * Programas de formación
     * 
     */
    Route::resource('programas-formacion', ProgramaFormacionController::class)->parameters(['programas-formacion' => 'programa-formacion'])->except(['show']);

    /**
     * Temáticas estratégicas
     * 
     */
    Route::resource('tematicas-estrategicas', TematicaEstrategicaController::class)->parameters(['tematicas-estrategicas' => 'tematica-estrategica'])->except(['show']);

    /**
     * Mesas técnicas
     * 
     */
    Route::resource('mesas-tecnicas', MesaTecnicaController::class)->parameters(['mesas-tecnicas' => 'mesa-tecnica'])->except(['show']);

    /**
     * Redes de conocimiento
     * 
     */
    Route::resource('redes-conocimiento', RedConocimientoController::class)->parameters(['redes-conocimiento' => 'red-conocimiento'])->except(['show']);

    /**
     * Grupos de investigación
     * 
     */
    Route::get('grupos-investigacion/{grupo_investigacion}/download/{formato}', [GrupoInvestigacionController::class, 'descargarFormato'])->name('grupos-investigacion.download');

    Route::resource('grupos-investigacion', GrupoInvestigacionController::class)->parameters(['grupos-investigacion' => 'grupo-investigacion'])->except(['show']);

    /**
     * Líneas de investigación
     * 
     * Trae los grupos de investigación
     * 
     */
    Route::get('web-api/grupos-investigacion', [WebController::class, 'gruposInvestigacion'])->name('web-api.grupos-investigacion');

    Route::get('web-api/centros-formacion-grupo-investigacion/{grupo_investigacion}', [WebController::class, 'centrosFormacionGrupoInvestigacion'])->name('web-api.centros-formacion-grupo-investigacion');

    Route::resource('grupos-investigacion.lineas-investigacion', LineaInvestigacionController::class)->parameters(['grupos-investigacion' => 'grupo-investigacion', 'lineas-investigacion' => 'linea-investigacion'])->except(['show']);

    /**
     * Semilleros de investigación
     * 
     * Trae las líneas de investigación
     */
    Route::get('web-api/lineas-investigacion/{centro_formacion}', [WebController::class, 'lineasInvestigacion'])->name('web-api.lineas-investigacion');

    Route::resource('grupos-investigacion.semilleros-investigacion', SemilleroInvestigacionController::class)->parameters(['grupos-investigacion' => 'grupo-investigacion', 'semilleros-investigacion' => 'semillero-investigacion'])->except(['show']);
    Route::get('grupos-investigacion/{grupo_investigacion}/semilleros-investigacion/{semillero_investigacion}/download/{formato}', [SemilleroInvestigacionController::class, 'descargarFormato'])->name('grupos-investigacion.semilleros-investigacion.download');

    Route::resource('grupos-investigacion.semilleros-investigacion', SemilleroInvestigacionController::class)->parameters(['grupos-investigacion' => 'grupo-investigacion', 'semilleros-investigacion' => 'semillero-investigacion'])->except(['show']);

    /**
     * Mesas sectoriales
     * 
     */
    Route::resource('mesas-sectoriales', MesaSectorialController::class)->parameters(['mesas-sectoriales' => 'mesa-sectorial'])->except(['show']);

    /**
     * Web api
     * 
     */
    Route::get('web-api/municipios', [WebController::class, 'municipios'])->name('web-api.municipios');

    /**
     * Web api
     * 
     * Trae las Tecnoacademias
     */
    Route::get('web-api/tecnoacademias', [WebController::class, 'tecnoacademias'])->name('web-api.tecnoacademias');

    /**
     * Web api
     * 
     * Trae las tecnoacademias centro_formacion
     */
    Route::get('web-api/centros-formacion/{centro_formacion}/tecnoacademias', [WebController::class, 'tecnoacademiasCentroFormacion'])->name('web-api.centros-formacion.tecnoacademias');

    /**
     * Web api
     * 
     * Trae las líneas tecnoacademia
     */
    Route::get('web-api/tecnoacademias/{tecnoacademia}/lineas-tecnoacademia', [WebController::class, 'líneasTecnoacademia'])->name('web-api.tecnoacademias.lineas-tecnoacademia');

    /**
     * Web api
     * 
     * Trae los nodos tecnoparque
     */
    Route::get('web-api/nodos-tecnoparque/{centro_formacion}', [WebController::class, 'nodosTecnoparque'])->name('web-api.nodos-tecnoparque');

    /**
     * Web api
     * 
     * Trae las líneas programáticas
     */
    Route::get('web-api/lineas-programaticas/{categoria_proyecto}', [WebController::class, 'líneasProgramaticas'])->name('web-api.lineas-programaticas');

    /**
     * Web api
     * 
     * Trae los programas de formación por línea de investigación
     */
    Route::get('web-api/linea-investigacion-programa-formacion/{linea_investigacion}', [WebController::class, 'líneaInvestigacionProgramaFormacion'])->name('web-api.linea-investigacion-programa-formacion');

    /**
     * Web api
     * 
     * Trae las redes de conocimiento 
     */
    Route::get('web-api/redes-conocimiento', [WebController::class, 'redesConocimiento'])->name('web-api.redes-conocimiento');

    /**
     * Web api
     * 
     * Trae las áreas de conocimiento
     */
    Route::get('web-api/areas-conocimiento', [WebController::class, 'areasConocimiento'])->name('web-api.areas-conocimiento');

    /**
     * Web api
     * 
     * Trae las subáreas de conocimiento
     */
    Route::get('web-api/subareas-conocimiento/{area_conocimiento}', [WebController::class, 'subareasConocimiento'])->name('web-api.subareas-conocimiento');

    /**
     * Web api
     * 
     * Trae las disciplinas de subáreas de conocimiento
     */
    Route::get('web-api/disciplinas-subarea-conocimiento/{subarea_conocimiento}', [WebController::class, 'disciplinasSubareaConocimiento'])->name('web-api.disciplinas-subarea-conocimiento');

    /**
     * Web api
     * 
     * Trae los actividades económicas
     */
    Route::get('web-api/actividades-economicas', [WebController::class, 'actividadesEconomicas'])->name('web-api.actividades-economicas');

    /**
     * Web api
     * 
     * Trae las temáticas estrategicas SENA
     */
    Route::get('web-api/tematicas-estrategicas', [WebController::class, 'tematicasEstrategicas'])->name('web-api.tematicas-estrategicas');

    /**
     * Web api
     * 
     * Trae las subtipologías Minciencias
     */
    Route::get('web-api/subtipologias-minciencias', [WebController::class, 'subtipologiasMinciencias'])->name('web-api.subtipologias-minciencias');

    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/finalizar-proyecto', [ProyectoController::class, 'summary'])->name('convocatorias.proyectos.summary');
    Route::put('convocatorias/{convocatoria}/proyectos/{proyecto}/finalizar-proyecto', [ProyectoController::class, 'finalizarProyecto'])->name('convocatorias.proyectos.finish');
    Route::put('convocatorias/{convocatoria}/proyectos/{proyecto}/comentario-proyecto', [ProyectoController::class, 'devolverProyecto'])->name('convocatorias.proyectos.return-project');
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/descargar-version/{version}', [ProyectoController::class, 'descargarPdf'])->name('convocatorias.proyectos.version');

    /**
     * Idi - Estrategia regional
     * 
     */
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/entidades-aliadas/{entidad_aliada}/{archivo}/download', [EntidadAliadaController::class, 'download'])->name('convocatorias.proyectos.entidades-aliadas.download');
    Route::resource('convocatorias.idi', IdiController::class)->parameters(['convocatorias' => 'convocatoria', 'idi' => 'idi'])->except(['show']);

    Route::put('convocatorias/{convocatoria}/idi/{idi}/column/{column}', [IdiController::class, 'updateLongColumn'])->name('convocatorias.idi.updateLongColumn');

    Route::resource('convocatorias.proyectos.entidades-aliadas', EntidadAliadaController::class)->parameters(['convocatorias' => 'convocatoria', 'proyectos' => 'proyecto', 'entidades-aliadas' => 'entidad-aliada'])->except(['show']);

    Route::resource('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada', MiembroEntidadAliadaController::class)->parameters(['convocatorias' => 'convocatoria', 'proyectos' => 'proyecto', 'entidades-aliadas' => 'entidad-aliada', 'miembros-entidad-aliada' => 'miembro-entidad-aliada'])->except(['show']);

    /**
     * Convocatorias
     * 
     */
    Route::get('convocatorias/{convocatoria}/dashboard', [ConvocatoriaController::class, 'dashboard'])->name('convocatorias.dashboard');
    Route::put('convocatorias/{convocatoria}/update-fase', [ConvocatoriaController::class, 'updateFase'])->name('convocatorias.update-fase');

    Route::resource('convocatorias', ConvocatoriaController::class)->parameters(['convocatorias' => 'convocatoria'])->except(['show']);

    /**
     * Muestra el árbol de objetivos
     * 
     */
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/arbol-objetivos', [ArbolProyectoController::class, 'showArbolObjetivos'])->name('convocatorias.proyectos.arbol-objetivos');
    // Actualiza el impacto en el arbol de objetivos
    Route::post('proyectos/{proyecto}/impacto/{impacto}', [ArbolProyectoController::class, 'updateImpacto'])->name('proyectos.impacto');
    Route::post('proyectos/{proyecto}/impacto/{impacto}/destroy', [ArbolProyectoController::class, 'destroyImpacto'])->name('proyectos.impacto.destroy');
    // Actualiza el impacto en el arbol de objetivos
    Route::post('proyectos/{proyecto}/resultado/{resultado}', [ArbolProyectoController::class, 'updateResultado'])->name('proyectos.resultado');
    Route::post('proyectos/{proyecto}/resultado/{resultado}/destroy', [ArbolProyectoController::class, 'destroyResultado'])->name('proyectos.resultado.destroy');
    // Actualiza el problema general del proyecto en el arbol de problemas
    Route::post('proyectos/{proyecto}/objetivo-general', [ArbolProyectoController::class, 'updateObjetivoGeneral'])->name('proyectos.objetivo-general');
    // Actualiza el objetivo especifico en el arbol de objetivos
    Route::post('proyectos/{proyecto}/objetivo-especifico/{objetivo_especifico}', [ArbolProyectoController::class, 'updateObjetivoEspecifico'])->name('proyectos.objetivo-especifico');
    Route::post('proyectos/{proyecto}/objetivo-especifico/{objetivo_especifico}/destroy', [ArbolProyectoController::class, 'destroyObjetivoEspecifico'])->name('proyectos.objetivo-especifico.destroy');
    // Actualiza la actividad en el arbol de objetivos
    Route::post('convocatorias/{convocatoria}/proyectos/{proyecto}/actividad/{actividad}', [ArbolProyectoController::class, 'updateActividad'])->name('proyectos.actividad');
    Route::post('proyectos/{proyecto}/actividad/{actividad}/destroy', [ArbolProyectoController::class, 'destroyActividad'])->name('proyectos.actividad.destroy');

    /**
     * Muestra el árbol de problemas
     * 
     */
    Route::get('convocatorias/{convocatoria}/proyectos/{proyecto}/arbol-problemas', [ArbolProyectoController::class, 'showArbolProblemas'])->name('convocatorias.proyectos.arbol-problemas');
    // Actualiza el problema general del proyecto en el arbol de problemas
    Route::post('proyectos/{proyecto}/problema-central', [ArbolProyectoController::class, 'updateProblemaCentral'])->name('proyectos.problema-central');
    // Actualiza efecto directo en el arbol de problemas
    Route::post('proyectos/{proyecto}/efecto-directo/{efecto_directo}', [ArbolProyectoController::class, 'updateEfectoDirecto'])->name('proyectos.efecto-directo');
    Route::post('proyectos/{proyecto}/efecto-directo/{efecto_directo}/destroy', [ArbolProyectoController::class, 'destroyEfectoDirecto'])->name('proyectos.efecto-directo.destroy');
    // Crea o Actualiza efecto indirecto en el arbol de problemas
    Route::post('proyectos/{proyecto}/efecto-indirecto/{efecto_directo}', [ArbolProyectoController::class, 'createOrUpdateEfectoIndirecto'])->name('proyectos.efecto-indirecto');
    Route::post('proyectos/{proyecto}/efecto-indirecto/{efecto_indirecto}/destroy', [ArbolProyectoController::class, 'destroyEfectoIndirecto'])->name('proyectos.efecto-indirecto.destroy');

    // Actualiza causa directa en el arbol de problemas
    Route::post('proyectos/{proyecto}/causa-directa/{causa_directa}', [ArbolProyectoController::class, 'updateCausaDirecta'])->name('proyectos.causa-directa');
    Route::post('proyectos/{proyecto}/causa-directa/{causa_directa}/destroy', [ArbolProyectoController::class, 'destroyCausaDirecta'])->name('proyectos.causa-directa.destroy');
    // Crea o Actualiza causa indirecta en el arbol de problemas
    Route::post('proyectos/{proyecto}/causa-indirecta/{causa_directa}', [ArbolProyectoController::class, 'createOrUpdateCausaIndirecta'])->name('proyectos.causa-indirecta');
    Route::post('proyectos/{proyecto}/causa-indirecta/{causa_indirecta}/destroy', [ArbolProyectoController::class, 'destroyCausaIndirecta'])->name('proyectos.causa-indirecta.destroy');

    /**
     * Productos
     * 
     */
    Route::resource('convocatorias.proyectos.productos', ProductoController::class)->parameters(['convocatorias' => 'convocatoria', 'proyectos' => 'proyecto', 'productos' => 'producto'])->except(['show']);

    /**
     * Actividades
     * 
     */
    Route::put('convocatorias/{convocatoria}/proyectos/{proyecto}/actividades/metodologia', [ActividadController::class, 'updateMetodologia'])->name('convocatorias.proyectos.metodologia');
    Route::resource('convocatorias.proyectos.actividades', ActividadController::class)->parameters(['convocatorias' => 'convocatoria', 'proyectos' => 'proyecto', 'actividades' => 'actividad'])->except(['show']);

    /**
     * Análisis de riesgos
     * 
     */
    Route::resource('convocatorias.proyectos.analisis-riesgos', AnalisisRiesgoController::class)->parameters(['analisis-riesgos' => 'analisis-riesgo'])->except(['show']);

    /**
     * Usuarios
     * 
     */
    Route::get('users/online', [UserController::class, 'enLinea'])->name('users.online');
    Route::resource('users',  UserController::class)->except(['show']);

    /**
     * Roles de sistema
     * 
     */
    Route::resource('roles', RoleController::class)->except(['show']);

    /**
     * Proyectos
     * 
     */
    Route::get('proyectos/activos', [ProyectoController::class, 'activos'])->name('proyectos.activos');
    Route::get('proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');
    Route::get('proyectos/{proyecto}/editar', [ProyectoController::class, 'editProyecto'])->name('proyectos.edit');
    Route::put('proyectos/{proyecto}/editar', [ProyectoController::class, 'update'])->name('proyectos.update');

    /**
     * Tokens
     * 
     */
    Route::prefix('tokens')->group(function () {
        Route::post('create', [ApiController::class, 'CreateToken'])->name('tokens.create');
    });
});

// Route::middleware(['checkToken'])->name('v1.')->prefix('api/v1')->group(function () {
//     // API Resources
//     Route::get('user_sennova', [ApiController::class, 'isUserSennova'])->name('user_sennova');
//     Route::get('user_sennova/{id}/projects', [ApiController::class, 'projectsByUser'])->name('projects_by_user');
//     Route::get('center/{id}/projects', [ApiController::class, 'projectsByCenter'])->name('projects_by_center');
//     Route::get('projects/{id}', [ApiController::class, 'summaryProject'])->name('project');
// });

require __DIR__ . '/auth.php';
