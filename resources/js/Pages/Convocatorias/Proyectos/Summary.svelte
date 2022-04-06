<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { page, useForm } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Stepper from '@/Shared/Stepper'
    import Dialog from '@/Shared/Dialog'
    import Button from '@/Shared/Button'
    import InfoMessage from '@/Shared/InfoMessage'
    import Label from '@/Shared/Label'
    import Password from '@/Shared/Password'

    export let errors
    export let convocatoria
    export let proyecto
    export let versiones
    export let problemaCentral
    export let efectosDirectos
    export let causasIndirectas
    export let causasDirectas
    export let efectosIndirectos
    export let objetivoGeneral
    export let resultados
    export let objetivosEspecificos
    export let actividades
    export let impactos
    export let resultadoProducto
    export let analisisRiesgo
    export let anexos
    export let generalidades
    export let metodologia
    export let propuestaSostenibilidad
    export let productosActividades
    export let soportesEstudioMercado
    export let estudiosMercadoArchivo

    $: $title = 'Finalizar proyecto'

    let finishProjectDialogOpen = errors.password != undefined ? true : false
    let sending = false

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let form = useForm({
        password: '',
    })

    function finishProject() {
        if (isSuperAdmin || checkPermission(authUser, [1, 3, 4])) {
            $form.put(route('convocatorias.proyectos.finish', [convocatoria.id, proyecto.id]), {
                onStart: () => (sending = true),
                onFinish: () => {
                    sending = false
                    $form.password = ''

                    if (!errors.password) {
                        finishProjectDialogOpen = false
                    }
                },
                preserveScroll: true,
            })
            $form.password = ''
        }
    }
</script>

<AuthenticatedLayout>
    <Stepper {convocatoria} {proyecto} />

    <div class="mt-20">
        {#if isSuperAdmin || checkPermission(authUser, [1, 3, 4])}
            {#if proyecto.finalizado == false && proyecto.modificable == true && generalidades && problemaCentral && efectosDirectos && efectosIndirectos && causasDirectas && causasIndirectas && objetivoGeneral && resultados && objetivosEspecificos && actividades && impactos && metodologia && propuestaSostenibilidad && productosActividades && resultadoProducto && analisisRiesgo && anexos && soportesEstudioMercado && estudiosMercadoArchivo}
                <InfoMessage class="mb-2" message="Si desea finalizar el proyecto de clic en <strong>Finalizar proyecto</strong> y a continuación, escriba la contraseña de su usuario. Se le notificará al dinamizador SENNOVA de su centro de formación para que haga la respectiva revisión y radicación del proyecto." />
                <Button on:click={(event) => (finishProjectDialogOpen = true)} variant="raised">Finalizar proyecto</Button>
            {:else if proyecto.finalizado == false}
                <InfoMessage class="mb-2" alertMsg={true}>
                    <p><strong>La información del proyecto está incompleta. Para poder finalizar el proyecto debe completar los siguientes ítems:</strong></p>
                    <ul class="list-disc p-4">
                        {#if !generalidades}
                            <li>Generalidades</li>
                        {/if}
                        {#if !problemaCentral}
                            <li>Problema central</li>
                        {/if}
                        {#if !efectosDirectos}
                            <li>Efectos directos</li>
                        {/if}
                        {#if !efectosIndirectos}
                            <li>Efectos indirectos</li>
                        {/if}
                        {#if !causasDirectas}
                            <li>Causas directas</li>
                        {/if}
                        {#if !causasIndirectas}
                            <li>Causas indirectas</li>
                        {/if}
                        {#if !objetivoGeneral}
                            <li>Objetivo general</li>
                        {/if}
                        {#if !resultados}
                            <li>Resultados (árbol de objetivos)</li>
                        {/if}
                        {#if !objetivosEspecificos}
                            <li>Objetivos específicos (árbol de objetivos)</li>
                        {/if}
                        {#if !actividades}
                            <li>Actividades (árbol de objetivos)</li>
                        {/if}
                        {#if !impactos}
                            <li>Impactos</li>
                        {/if}
                        {#if !metodologia}
                            <li>Metodología (Metodología y actividades)</li>
                        {/if}
                        {#if !propuestaSostenibilidad}
                            <li>Propuesta de sostenibilidad (Cadena de valor)</li>
                        {/if}

                        {#if !productosActividades}
                            <li>Hay productos sin actividades relacionadas</li>
                        {/if}
                        {#if !resultadoProducto}
                            <li>Hay resultados sin productos relacionados</li>
                        {/if}
                        {#if !analisisRiesgo}
                            <li>Faltan análisis de riesgos</li>
                        {/if}
                        {#if !soportesEstudioMercado}
                            <li>Hay estudios de mercado con menos de dos soportes</li>
                        {/if}
                        {#if !estudiosMercadoArchivo}
                            <li>Hay rubros presupuestales que no tienen el estudio de mercado cargado</li>
                        {/if}
                    </ul>
                </InfoMessage>
            {/if}
        {/if}
    </div>
    {#if proyecto.finalizado == true && !checkRole(authUser, [1, 4])}
        <hr class="mt-10 mb-10" />
        <InfoMessage class="mb-2" message="El proyecto se ha finalizado con éxito." />
    {/if}
    <hr class="mt-10 mb-10" />
    <div>
        <InfoMessage>
            <h1><strong>Historial de acciones</strong></h1>
            {#if proyecto.logs}
                <ul>
                    {#each proyecto.logs as log}
                        <li>{log.created_at} - {JSON.parse(log.data).subject}</li>
                    {/each}
                </ul>
            {:else}
                <p>No se ha generado un historial aún</p>
            {/if}
        </InfoMessage>
    </div>
    <hr class="mt-10 mb-10" />
    <div>
        <InfoMessage>
            <h1><strong>Versiones del proyecto</strong></h1>
            El PDF se generará una vez finalice la fase de formulación.
            {#if versiones}
                <ul>
                    {#each versiones as version}
                        <li>
                            {version.version}.pdf -
                            {#if version.estado == 1}
                                <a href={route('convocatorias.proyectos.version', [convocatoria.id, proyecto.id, version.version])}>Descargar</a>
                            {:else}
                                Generando una nueva versión, regrese pronto.
                            {/if}
                        </li>
                    {/each}
                </ul>
            {:else}
                <p>No se ha generado un historial aún</p>
            {/if}
        </InfoMessage>
    </div>

    <Dialog bind:open={finishProjectDialogOpen}>
        <div slot="titulo" class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Finalizar proyecto
        </div>
        <div slot="content">
            <InfoMessage class="mb-2" message="¿Está seguro (a) que desea finalizar el proyecto?<br />Una vez finalizado el proyecto no se podrá modificar." />

            <form on:submit|preventDefault={finishProject} id="finalizar-proyecto" class="mt-10 mb-28" on:load={($form.password = '')}>
                <Label labelFor="password" value="Ingrese su contraseña para confirmar que desea finalizar este proyecto" class="mb-4" />
                <Password id="password" class="w-full" bind:value={$form.password} error={errors.password} required autocomplete="current-password" />
            </form>
        </div>
        <div slot="actions">
            <div class="p-4">
                <Button on:click={(event) => (finishProjectDialogOpen = false)} variant={null}>Cancelar</Button>
                <Button variant="raised" form="finalizar-proyecto">Finalizar proyecto</Button>
            </div>
        </div>
    </Dialog>
</AuthenticatedLayout>
