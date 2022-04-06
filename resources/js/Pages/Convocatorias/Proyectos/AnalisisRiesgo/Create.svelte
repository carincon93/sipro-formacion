<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import Textarea from '@/Shared/Textarea'
    import InfoMessage from '@/Shared/InfoMessage'

    export let convocatoria
    export let proyecto
    export let errors
    export let nivelesRiesgo
    export let tiposRiesgo
    export let probabilidadesRiesgo
    export let impactosRiesgo

    $: $title = 'Crear análisis de riesgo'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        nivel: '',
        tipo: '',
        descripcion: '',
        impacto: '',
        probabilidad: '',
        efectos: '',
        medidas_mitigacion: '',
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true)) {
            $form.post(route('convocatorias.proyectos.analisis-riesgos.store', [convocatoria.id, proyecto.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
            })
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1>
                    {#if isSuperAdmin || checkPermission(authUser, [1])}
                        <a use:inertia href={route('convocatorias.proyectos.analisis-riesgos.index', [convocatoria.id, proyecto.id])} class="text-indigo-400 hover:text-indigo-600">Análisis de riesgos</a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    Crear
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true) ? undefined : true}>
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
                {#if isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true)}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" tipo="submit">Crear análisis de riesgo</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
