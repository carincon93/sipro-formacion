<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Label from '@/Shared/Label'
    import Button from '@/Shared/Button'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import Textarea from '@/Shared/Textarea'
    import Dialog from '@/Shared/Dialog'
    import InfoMessage from '@/Shared/InfoMessage'

    export let errors
    export let convocatoria
    export let proyecto
    export let analisisRiesgo
    export let nivelesRiesgo
    export let tiposRiesgo
    export let probabilidadesRiesgo
    export let impactosRiesgo

    $: $title = analisisRiesgo ? 'Análisis de riesgos - ' + analisisRiesgo.nivel : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        nivel: {
            value: nivelesRiesgo.find((item) => item.label == analisisRiesgo.nivel)?.value,
            label: nivelesRiesgo.find((item) => item.label == analisisRiesgo.nivel)?.label,
        },
        tipo: {
            value: tiposRiesgo.find((item) => item.label == analisisRiesgo.tipo)?.value,
            label: tiposRiesgo.find((item) => item.label == analisisRiesgo.tipo)?.label,
        },
        descripcion: analisisRiesgo.descripcion,
        impacto: {
            value: impactosRiesgo.find((item) => item.label == analisisRiesgo.impacto)?.value,
            label: impactosRiesgo.find((item) => item.label == analisisRiesgo.impacto)?.label,
        },
        probabilidad: {
            value: probabilidadesRiesgo.find((item) => item.label == analisisRiesgo.probabilidad)?.value,
            label: probabilidadesRiesgo.find((item) => item.label == analisisRiesgo.probabilidad)?.label,
        },
        efectos: analisisRiesgo.efectos,
        medidas_mitigacion: analisisRiesgo.medidas_mitigacion,
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)) {
            $form.put(route('convocatorias.proyectos.analisis-riesgos.update', [convocatoria.id, proyecto.id, analisisRiesgo.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)) {
            $form.delete(route('convocatorias.proyectos.analisis-riesgos.destroy', [convocatoria.id, proyecto.id, analisisRiesgo.id]))
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin || checkPermission(authUser, [3, 4, 14])}
                        <a use:inertia href={route('convocatorias.proyectos.analisis-riesgos.index', [convocatoria.id, proyecto.id])} class="text-indigo-400 hover:text-indigo-600">Análisis de riesgos</a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {analisisRiesgo.tipo}
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true) ? undefined : true}>
                <div class="mt-4">
                    <Label required class="mb-4" labelFor="nivel" value="Nivel de riesgo" />
                    <Select id="nivel" items={nivelesRiesgo} bind:selectedValue={$form.nivel} error={errors.nivel} autocomplete="off" placeholder="Seleccione el nivel del riesgo" required />
                </div>

                <div class="mt-8">
                    <Label required class="mb-4" labelFor="tipo" value="Tipo de riesgo" />
                    <Select id="tipo" items={tiposRiesgo} bind:selectedValue={$form.tipo} error={errors.tipo} autocomplete="off" placeholder="Seleccione el tipo de riesgo" required />
                    {#if $form.tipo?.value == 1}
                        <InfoMessage message="Es la probabilidad de variaciones en las condiciones del mercado como el precio, la calidad y la disponibilidad de los materiales e insumos, la competencia (oferta/demanda) del producto/servicios ofrecidos, la tasa de cambiaria y los asociados a la tecnología utilizada (obsolescencia)." />
                    {:else if $form.tipo?.value == 2}
                        <InfoMessage message="Es toda posible contingencia que pueda provocar pérdidas en el desarrollo del proyecto por causa de errores humanos, de errores tecnológicos, de procesos internos defectuosos o fallidos. Esta clase de riesgo es inherente a todos los sistemas y procesos realizados por humanos." />
                    {:else if $form.tipo?.value == 3}
                        <InfoMessage
                            message="Son los obstáculos legales o normativos que pueden afectar el desarrollo del proyecto. Por ejemplo: nuevos requisitos legales, cambios reglamentarios o gubernamentales directamente relacionados con el entorno que se desarrolla el proyecto, ausencia y/o deficiencia de documentación, errores en los contratos, incapacidad del proyecto de cumplir lo pactado."
                        />
                    {:else if $form.tipo?.value == 4}
                        <InfoMessage
                            message="Es la probabilidad de incurrir en pérdidas originadas por la deficiencia en la planeación, procesos, controles y/o falta de idoneidad y competencia del personal. Por ejemplo: falta de planeación del proyecto, estructura organizacional incoherente, falta de liderazgo, falta de integración entre la dirección y la parte operativa y/o productiva, ineficiencia en la adaptación a los cambios del entorno, toma de decisiones por información incompleta."
                        />
                    {/if}
                </div>

                <div class="mt-8">
                    <Textarea label="Descripción" maxlength="800" id="descripcion" error={errors.descripcion} bind:value={$form.descripcion} required />
                </div>

                <div class="mt-8">
                    <Label required class="mb-4" labelFor="probabilidad" value="Probabilidad" />
                    <Select id="probabilidad" items={probabilidadesRiesgo} bind:selectedValue={$form.probabilidad} error={errors.probabilidad} autocomplete="off" placeholder="Seleccione la probabilidad" required />
                </div>

                <div class="mt-8">
                    <Label required class="mb-4" labelFor="impacto" value="Impacto" />
                    <Select id="impacto" items={impactosRiesgo} bind:selectedValue={$form.impacto} error={errors.impacto} autocomplete="off" placeholder="Seleccione la probabilidad" required />
                </div>

                <div class="mt-8">
                    <Textarea label="Efectos" maxlength="800" id="efectos" error={errors.efectos} bind:value={$form.efectos} required />
                </div>

                <div class="mt-8">
                    <Textarea label="Medidas de mitigación" maxlength="800" id="medidas_mitigacion" error={errors.medidas_mitigacion} bind:value={$form.medidas_mitigacion} required />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)}
                    <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar análisis de riesgo </button>
                {/if}
                {#if isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar análisis de riesgo</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
    <Dialog bind:open={dialogOpen}>
        <div slot="title" class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Eliminar recurso
        </div>
        <div slot="content">
            <p>
                ¿Está seguro(a) que desea eliminar este recurso?
                <br />
                Todos los datos se eliminarán de forma permanente.
                <br />
                Está acción no se puede deshacer.
            </p>
        </div>
        <div slot="actions">
            <div class="p-4">
                <Button on:click={(event) => (dialogOpen = false)} variant={null}>Cancelar</Button>
                <Button variant="raised" on:click={destroy}>Confirmar</Button>
            </div>
        </div>
    </Dialog>
</AuthenticatedLayout>
