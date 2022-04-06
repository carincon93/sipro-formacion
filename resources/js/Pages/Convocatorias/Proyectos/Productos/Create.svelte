<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Label from '@/Shared/Label'
    import InputError from '@/Shared/InputError'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'
    import Textarea from '@/Shared/Textarea'
    import InfoMessage from '@/Shared/InfoMessage'

    export let errors
    export let convocatoria
    export let proyecto
    export let resultados
    export let tiposProducto

    $: $title = 'Crear producto'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        nombre: '',
        resultado_id: null,
        subtipologia_minciencias_id: null,
        fecha_inicio: '',
        fecha_finalizacion: '',
        indicador: '',
        tipo: null,
        tatp_servicio_tecnologico: proyecto.ta || proyecto.tp || proyecto.servicio_tecnologico ? true : false,
        valor_proyectado: null,
        medio_verificacion: '',
        nombre_indicador: '',
        formula_indicador: '',
        actividad_id: [],
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true)) {
            $form.post(route('convocatorias.proyectos.productos.store', [convocatoria.id, proyecto.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
            })
        }
    }

    let actividades = []
    let resultado_id = null

    $: if (resultado_id?.value) {
        $form.actividad_id = []
        $form.resultado_id = resultado_id.value
        actividades = resultado_id.actividades
    }

    console.log(proyecto.servicio_tecnologico)
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1>
                    {#if isSuperAdmin || checkPermission(authUser, [1])}
                        <a use:inertia href={route('convocatorias.proyectos.productos.index', [convocatoria.id, proyecto.id])} class="text-indigo-400 hover:text-indigo-600"> Productos </a>
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
                <div class="mt-8 mb-8">
                    <Label class="text-center" required value="Fecha de ejecución" />
                    <div class="mt-4 flex items-start justify-around">
                        <div class="mt-4 flex">
                            <Label labelFor="fecha_inicio" value="Del" />
                            <div class="ml-4">
                                <input id="fecha_inicio" type="date" class="mt-1 block w-full p-4" min={proyecto.fecha_inicio} max={proyecto.fecha_finalizacion} bind:value={$form.fecha_inicio} required />
                            </div>
                        </div>
                        <div class="mt-4 flex">
                            <Label labelFor="fecha_finalizacion" value="hasta" />
                            <div class="ml-4">
                                <input id="fecha_finalizacion" type="date" class="mt-1 block w-full p-4" min={proyecto.fecha_inicio} max={proyecto.fecha_finalizacion} bind:value={$form.fecha_finalizacion} required />
                            </div>
                        </div>
                    </div>
                    {#if errors.fecha_inicio || errors.fecha_finalizacion}
                        <InputError message={errors.fecha_inicio || errors.fecha_finalizacion} />
                    {/if}
                </div>

                <hr />

                <div class="mt-8">
                    {#if $form.tatp_servicio_tecnologico}
                        <InfoMessage>
                            <p>
                                Los productos pueden corresponder a bienes o servicios. Un bien es un objeto tangible, almacenable o transportable, mientras que el servicio es una prestación intangible.
                                <br />
                                El producto debe cumplir con la siguiente estructura:
                                <br />
                                Cuando el producto es un bien: nombre del bien + la condición deseada. Ejemplo: Vía construida.
                                <br />
                                Cuando el producto es un servicio: nombre del servicio + el complemento. Ejemplo: Servicio de asistencia técnica para el mejoramiento de hábitos alimentarios
                            </p>
                        </InfoMessage>
                    {/if}
                    <Textarea label="Descripción" maxlength="40000" id="nombre" error={errors.nombre} bind:value={$form.nombre} required />
                </div>

                <div class="mt-8">
                    <Label required class="mb-4" labelFor="resultado_id" value="Resultado" />
                    <Select id="resultado_id" items={resultados} bind:selectedValue={resultado_id} error={errors.resultado_id} autocomplete="off" placeholder="Seleccione un resultado" required />
                </div>
                {#if proyecto.servicio_tecnologico == null}
                    <div class="mt-8">
                        <Label required labelFor="indicador" value="Indicador" />

                        {#if $form.tatp_servicio_tecnologico == true}
                            <InfoMessage class="mb-2" message="Deber ser medible y con una fórmula. Por ejemplo: (# metodologías validadas/# metodologías totales) X 100" />
                        {:else}
                            <InfoMessage class="mb-2" message="Especifique los medios de verificación para validar los logros del proyecto." />
                        {/if}
                        <Textarea maxlength="40000" id="indicador" error={errors.indicador} bind:value={$form.indicador} required />
                    </div>
                {/if}

                {#if $form.tatp_servicio_tecnologico == false}
                    <div class="mt-8">
                        <Label required class="mb-4" labelFor="subtipologia_minciencias_id" value="Subtipología Minciencias" />
                        <DynamicList id="subtipologia_minciencias_id" bind:value={$form.subtipologia_minciencias_id} routeWebApi={route('web-api.subtipologias-minciencias')} placeholder="Busque por el nombre de la subtipología Minciencias" message={errors.subtipologia_minciencias_id} required />
                    </div>

                    <div class="mt-8">
                        <Select id="tipo-producto" items={tiposProducto} bind:selectedValue={$form.tipo} error={errors.tipo} autocomplete="off" placeholder="Seleccione un tipo" required />
                    </div>
                {:else if proyecto.ta || proyecto.tp}
                    <div class="mt-8">
                        <Textarea label="Meta" maxlength="40000" id="valor_proyectado" error={errors.valor_proyectado} bind:value={$form.valor_proyectado} required />
                    </div>
                {/if}

                {#if $form.tatp_servicio_tecnologico == true}
                    <div class="mt-8">
                        <Label required labelFor="medio_verificacion" value="Medio de verificación" />

                        {#if proyecto.servicio_tecnologico}
                            <InfoMessage message="Los medios de verificación corresponden a las evidencias y/o fuentes de información en las que está disponibles los registros, la información necesaria y suficiente. Dichos medios pueden ser documentos oficiales, informes, evaluaciones, encuestas, documentos o reportes internos que genera el proyecto, entre otros." />
                        {:else}
                            <InfoMessage message="Especifique los medios de verificación para validar los logros del objetivo específico." />
                        {/if}

                        <Textarea maxlength="40000" id="medio_verificacion" error={errors.medio_verificacion} bind:value={$form.medio_verificacion} required />
                    </div>
                {/if}

                {#if proyecto.servicio_tecnologico}
                    <div class="mt-8">
                        <Label required labelFor="nombre_indicador" value="Nombre del Indicador del producto" />

                        <InfoMessage message="El indicador debe mantener una estructura coherente. Esta se compone de dos elementos: en primer lugar, debe ir el objeto a cuantificar, descrito por un sujeto y posteriormente la condición deseada, definida a través de un verbo en participio. Por ejemplo: Kilómetros de red vial nacional construidos." />
                        <Textarea maxlength="40000" id="nombre_indicador" error={errors.nombre_indicador} bind:value={$form.nombre_indicador} required />
                    </div>

                    <div class="mt-8">
                        <Label required labelFor="indicador" value="Fórmula del Indicador del producto" />

                        <InfoMessage
                            message="El método de cálculo debe ser una expresión matemática definida de manera adecuada y de fácil comprensión, es decir, deben quedar claras cuáles son las variables utilizadas. Los métodos de cálculo más comunes son el porcentaje, la tasa de variación, la razón y el número índice. Aunque éstos no son las únicas expresiones para los indicadores, sí son las más frecuentes."
                        />
                        <Textarea maxlength="40000" id="indicador" error={errors.indicador} bind:value={$form.indicador} required />
                    </div>
                {/if}

                <h6 class="mt-20 mb-12 text-2xl">Actividades</h6>
                <div class="bg-white rounded shadow overflow-hidden">
                    <div class="p-4">
                        <Label required class="mb-4" labelFor="actividad_id" value="Relacione alguna actividad" />
                        <InputError message={errors.actividad_id} />
                    </div>
                    <div class="grid grid-cols-2">
                        {#each actividades as { id, descripcion }, i}
                            <Label class="p-3 border-t border-b flex items-center text-sm" labelFor={'linea-tecnologica-' + id} value={descripcion} />

                            <div class="border-b border-t flex items-center justify-center">
                                <input type="checkbox" bind:group={$form.actividad_id} id={'linea-tecnologica-' + id} value={id} class="rounded text-indigo-500" />
                            </div>
                        {/each}
                        {#if actividades.length == 0}
                            <p class="p-4">Sin información registrada</p>
                        {/if}
                    </div>
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true)}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Crear producto</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
