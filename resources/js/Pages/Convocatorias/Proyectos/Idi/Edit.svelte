<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { useForm, page } from '@inertiajs/inertia-svelte'
    import { Inertia } from '@inertiajs/inertia'
    import { route, checkRole, checkPermission, monthDiff } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import axios from 'axios'
    import { onMount } from 'svelte'

    import Button from '@/Shared/Button'
    import Input from '@/Shared/Input'
    import InputError from '@/Shared/InputError'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Stepper from '@/Shared/Stepper'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'
    import Textarea from '@/Shared/Textarea'
    import InfoMessage from '@/Shared/InfoMessage'
    import Password from '@/Shared/Password'
    import Select from '@/Shared/Select'
    import SelectMulti from '@/Shared/SelectMulti'
    import Switch from '@/Shared/Switch'
    import Checkbox from '@smui/checkbox'
    import FormField from '@smui/form-field'
    import Radio from '@smui/radio'
    import Dialog from '@/Shared/Dialog'

    export let errors
    export let convocatoria
    export let idi
    export let mesasSectoriales
    export let mesasSectorialesRelacionadas
    export let opcionesIDiDropdown
    export let proyectoMunicipios
    export let proyectoProgramasFormacion
    export let proyectoProgramasFormacionArticulados
    export let versiones

    $: $title = idi ? idi.titulo : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let municipios
    let programasFormacion
    let programasFormacionArticular
    let dialogOpen = errors.password != undefined ? true : false
    let proyectoDialogOpen = true
    let sending = false
    let opcionesSiNo = [
        { value: 1, label: 'Si' },
        { value: 2, label: 'No' },
    ]

    let lineasTecnologicas = []

    let tieneVideo = idi.video != null
    let requiereJustificacionIndustria4 = idi.justificacion_industria_4 != null
    let requiereJustificacionEconomiaNaranja = idi.justificacion_economia_naranja != null
    let requiereJustificacionPoliticaDiscapacidad = idi.justificacion_politica_discapacidad != null

    let resumenForm = useForm({
        resumen: idi.resumen,
    })

    let formAntecedentes = useForm({
        antecedentes: idi.antecedentes,
    })

    let formMarcoConceptual = useForm({
        marco_conceptual: idi.marco_conceptual,
    })

    let formJustificacionIndustria4 = useForm({
        justificacion_industria_4: idi.justificacion_industria_4,
    })

    let formJustificacionEconomiaNaranja = useForm({
        justificacion_economia_naranja: idi.justificacion_economia_naranja,
    })

    let formJustificacionPoliticaDiscapacidad = useForm({
        justificacion_politica_discapacidad: idi.justificacion_politica_discapacidad,
    })

    let formBibliografia = useForm({
        bibliografia: idi.bibliografia,
    })

    let formImpactoMunicipios = useForm({
        impacto_municipios: idi.impacto_municipios,
    })

    let formImpactoCentroFormacion = useForm({
        impacto_centro_formacion: idi.impacto_centro_formacion,
    })

    let form = useForm({
        centro_formacion_id: idi.proyecto?.centro_formacion_id,
        linea_investigacion_id: idi.linea_investigacion_id,
        area_conocimiento_id: idi.disciplina_subarea_conocimiento.subarea_conocimiento.area_conocimiento_id,
        subarea_conocimiento_id: idi.disciplina_subarea_conocimiento.subarea_conocimiento_id,
        disciplina_subarea_conocimiento_id: idi.disciplina_subarea_conocimiento_id,
        tematica_estrategica_id: idi.tematica_estrategica_id,
        red_conocimiento_id: idi.red_conocimiento_id,
        actividad_economica_id: idi.actividad_economica_id,
        titulo: idi.titulo,
        fecha_inicio: idi.fecha_inicio,
        fecha_finalizacion: idi.fecha_finalizacion,
        max_meses_ejecucion: idi.max_meses_ejecucion,
        video: idi.video,
        numero_aprendices: idi.numero_aprendices,
        municipios: proyectoMunicipios.length > 0 ? proyectoMunicipios : null,
        programas_formacion: proyectoProgramasFormacion.length > 0 ? proyectoProgramasFormacion : null,
        programas_formacion_articulados: proyectoProgramasFormacionArticulados.length > 0 ? proyectoProgramasFormacionArticulados : null,
        muestreo: idi.muestreo,
        actividades_muestreo: idi.actividades_muestreo,
        objetivo_muestreo: idi.objetivo_muestreo,
        recoleccion_especimenes: {
            value: idi.recoleccion_especimenes,
            label: opcionesSiNo.find((item) => item.value == idi.recoleccion_especimenes)?.label,
        },
        relacionado_plan_tecnologico: {
            value: idi.relacionado_plan_tecnologico,
            label: opcionesIDiDropdown.find((item) => item.value == idi.relacionado_plan_tecnologico)?.label,
        },
        relacionado_agendas_competitividad: {
            value: idi.relacionado_agendas_competitividad,
            label: opcionesIDiDropdown.find((item) => item.value == idi.relacionado_agendas_competitividad)?.label,
        },
        relacionado_mesas_sectoriales: {
            value: idi.relacionado_mesas_sectoriales,
            label: opcionesIDiDropdown.find((item) => item.value == idi.relacionado_mesas_sectoriales)?.label,
        },
        relacionado_tecnoacademia: {
            value: idi.relacionado_tecnoacademia,
            label: opcionesIDiDropdown.find((item) => item.value == idi.relacionado_tecnoacademia)?.label,
        },
        mesa_sectorial_id: mesasSectorialesRelacionadas,
    })

    $: if ($form.fecha_inicio && $form.fecha_finalizacion) {
        $form.max_meses_ejecucion = monthDiff($form.fecha_inicio, $form.fecha_finalizacion)
    }

    onMount(() => {
        getMunicipios()
        getProgramasFormacion()
        getProgramasFormacionArticular()
    })

    async function syncColumnLong(column, form) {
        return new Promise((resolve) => {
            if (typeof column !== 'undefined' && typeof form !== 'undefined' && (isSuperAdmin || (checkPermission(authUser, [3, 4]) && idi.proyecto.modificable == true))) {
                //guardar
                Inertia.put(
                    route('convocatorias.idi.updateLongColumn', [convocatoria.id, idi.id, column]),
                    { [column]: form[column] },
                    {
                        onStart: () => (sending = true),
                        onError: (resp) => ((sending = false), resolve(resp)),
                        onFinish: () => ((sending = false), resolve({})),
                        preserveScroll: true,
                    },
                )
            } else {
                resolve({})
            }
        })
    }

    async function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [3, 4]) && idi.proyecto.modificable == true)) {
            if ($form.relacionado_tecnoacademia?.value != 1) {
                $form.tecnoacademia_id = {}
                lineasTecnologicas = []
            }

            $form.put(route('convocatorias.idi.update', [convocatoria.id, idi.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    let deleteForm = useForm({
        password: '',
    })

    function destroy() {
        if (isSuperAdmin || (checkPermission(authUser, [4]) && idi.proyecto.modificable == true)) {
            $deleteForm.delete(route('convocatorias.idi.destroy', [convocatoria.id, idi.id]), {
                preserveScroll: true,
            })
        }
    }

    async function getMunicipios() {
        let res = await axios.get(route('web-api.municipios'))
        if (res.status == '200') {
            municipios = res.data
        }
    }

    async function getProgramasFormacion() {
        let res = await axios.get(route('web-api.programas-formacion', $form.centro_formacion_id))
        if (res.status == '200') {
            programasFormacion = res.data
        }
    }

    async function getProgramasFormacionArticular() {
        let res = await axios.get(route('web-api.programas-formacion-articulados'))
        if (res.status == '200') {
            programasFormacionArticular = res.data
        }
    }
</script>

<AuthenticatedLayout>
    <Stepper {convocatoria} proyecto={idi} />

    <form on:submit|preventDefault={submit}>
        <fieldset class="p-8" disabled={isSuperAdmin || (checkPermission(authUser, [3, 4]) && idi.proyecto.modificable == true) ? undefined : true}>
            <div class="mt-28">
                <Label required labelFor="titulo" class="font-medium inline-block mb-10 text-center text-gray-700 text-sm w-full" value="Descripción llamativa que orienta el enfoque del proyecto, indica el cómo y el para qué. (Máximo 20 palabras)" />
                <Textarea label="Título" id="titulo" sinContador={true} error={errors.titulo} bind:value={$form.titulo} classes="bg-transparent block border-0 {errors.titulo ? '' : 'outline-none-important'} mt-1 outline-none text-4xl text-center w-full" required />
            </div>
            <div class="mt-44">
                <p class="text-center">Fecha de ejecución</p>
                <small class="text-red-400 block text-center"> * Campo obligatorio </small>
                <InfoMessage message={convocatoria.fecha_maxima_idi} class="my-5" />

                <div class="mt-4 flex items-start justify-around">
                    <div class="mt-4 flex {errors.fecha_inicio ? '' : 'items-center'}">
                        <Label required labelFor="fecha_inicio" class={errors.fecha_inicio ? 'top-3.5 relative' : ''} value="Del" />
                        <div class="ml-4">
                            <input id="fecha_inicio" type="date" class="mt-1 block w-full p-4" min={convocatoria.min_fecha_inicio_proyectos_idi} max={convocatoria.max_fecha_finalizacion_proyectos_idi} bind:value={$form.fecha_inicio} required />
                        </div>
                    </div>
                    <div class="mt-4 flex {errors.fecha_finalizacion ? '' : 'items-center'}">
                        <Label required labelFor="fecha_finalizacion" class={errors.fecha_finalizacion ? 'top-3.5 relative' : ''} value="hasta" />
                        <div class="ml-4">
                            <input id="fecha_finalizacion" type="date" class="mt-1 block w-full p-4" min={convocatoria.min_fecha_inicio_proyectos_idi} max={convocatoria.max_fecha_finalizacion_proyectos_idi} bind:value={$form.fecha_finalizacion} required />
                        </div>
                    </div>
                </div>
                {#if errors.fecha_inicio || errors.fecha_finalizacion || errors.max_meses_ejecucion}
                    <div class="mb-20 flex justify-center mt-4">
                        <InputError classes="text-center" message={errors.fecha_inicio} />
                        <InputError classes="text-center" message={errors.fecha_finalizacion} />
                        <InputError classes="text-center" message={errors.max_meses_ejecucion} />
                    </div>
                {/if}
            </div>
            <fieldset disabled>
                <div class="mt-44 grid grid-cols-2">
                    <div>
                        <Label required class="mb-4" labelFor="centro_formacion_id" value="Centro de formación" />
                        <small> Nota: El Centro de Formación relacionado es el ejecutor del proyecto </small>
                    </div>
                    <div>
                        {idi.proyecto.centro_formacion.nombre}
                    </div>
                </div>

                {#if $form.centro_formacion_id}
                    <div class="mt-44 grid grid-cols-2">
                        <div>
                            <Label required class="mb-4" labelFor="linea_investigacion_id" value="Línea de investigación" />
                        </div>
                        <div>
                            <DynamicList id="linea_investigacion_id" bind:value={$form.linea_investigacion_id} routeWebApi={route('web-api.lineas-investigacion', $form.centro_formacion_id)} classes="min-h" placeholder="Busque por el nombre de la línea de investigación, centro de formación, grupo de investigación o regional" message={errors.linea_investigacion_id} required />
                        </div>
                    </div>
                {/if}
            </fieldset>
            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="red_conocimiento_id" value="Red de conocimiento sectorial" />
                </div>
                <div>
                    <DynamicList id="red_conocimiento_id" bind:value={$form.red_conocimiento_id} routeWebApi={route('web-api.redes-conocimiento')} classes="min-h" placeholder="Busque por el nombre de la red de conocimiento sectorial" message={errors.red_conocimiento_id} required />
                </div>
            </div>
            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="area_conocimiento_id" value="Área de conocimiento" />
                </div>
                <div>
                    <DynamicList id="area_conocimiento_id" bind:value={$form.area_conocimiento_id} routeWebApi={route('web-api.areas-conocimiento')} classes="min-h" placeholder="Busque por el nombre de la área de conocimiento" message={errors.area_conocimiento_id} required />
                </div>
            </div>
            {#if $form.area_conocimiento_id}
                <div class="mt-44 grid grid-cols-2">
                    <div>
                        <Label required class="mb-4" labelFor="subarea_conocimiento_id" value="Subárea de conocimiento" />
                    </div>
                    <div>
                        <DynamicList id="subarea_conocimiento_id" bind:value={$form.subarea_conocimiento_id} routeWebApi={route('web-api.subareas-conocimiento', $form.area_conocimiento_id)} classes="min-h" placeholder="Busque por el nombre de la subárea de conocimiento" message={errors.subarea_conocimiento_id} required />
                    </div>
                </div>
            {/if}
            {#if $form.subarea_conocimiento_id}
                <div class="mt-44 grid grid-cols-2">
                    <div>
                        <Label required class="mb-4" labelFor="disciplina_subarea_conocimiento_id" value="Disciplina de la subárea de conocimiento" />
                    </div>
                    <div>
                        <DynamicList id="disciplina_subarea_conocimiento_id" bind:value={$form.disciplina_subarea_conocimiento_id} routeWebApi={route('web-api.disciplinas-subarea-conocimiento', $form.subarea_conocimiento_id)} classes="min-h" placeholder="Busque por el nombre de la disciplina de subáreas de conocimiento" message={errors.disciplina_subarea_conocimiento_id} required />
                    </div>
                </div>
            {/if}
            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="actividad_economica_id" value="¿En cuál de estas actividades económicas se puede aplicar el proyecto?" />
                </div>
                <div>
                    <DynamicList id="actividad_economica_id" bind:value={$form.actividad_economica_id} routeWebApi={route('web-api.actividades-economicas')} placeholder="Busque por el nombre de la actividad económica" classes="min-h" message={errors.actividad_economica_id} required />
                </div>
            </div>
            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="tematica_estrategica_id" value="Temática estratégica SENA" />
                </div>
                <div>
                    <DynamicList id="tematica_estrategica_id" bind:value={$form.tematica_estrategica_id} routeWebApi={route('web-api.tematicas-estrategicas')} placeholder="Busque por el nombre de la temática estrategica SENA" message={errors.tematica_estrategica_id} required />
                </div>
            </div>
            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label labelFor="video" value="¿El proyecto tiene video?" />
                </div>
                <div>
                    <Switch bind:checked={tieneVideo} />
                    {#if tieneVideo}
                        <InfoMessage class="mb-2" message="Video de 3 minutos, en donde se presente de manera sencilla y dinámica la justificación del proyecto, la problemática, el objetivo general, los objetivos específicos, las actividades, los productos y su impacto en el marco del mecanismo de participación seleccionado como regional." />
                        <Input label="Link del video" id="video" type="url" class="mt-1" error={errors.video} placeholder="Link del video del proyecto https://www.youtube.com/watch?v=gmc4tk" bind:value={$form.video} required={!tieneVideo ? undefined : 'required'} />
                    {/if}
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label id="justificacion_industria_4" value="¿El proyecto está relacionado con la industria 4.0?" />
                </div>
                <div>
                    <div class="flex items-center mb-14">
                        <Switch bind:checked={requiereJustificacionIndustria4} />
                    </div>

                    {#if requiereJustificacionIndustria4}
                        <InfoMessage class="mb-2" message="Si el proyecto está relacionado con la industria 4.0 por favor realice la justificación." />
                        <Textarea label="Justificación" maxlength="40000" id="justificacion_industria_4" error={errors.justificacion_industria_4} bind:value={$formJustificacionIndustria4.justificacion_industria_4} on:input={() => syncColumnLong('justificacion_industria_4', $formJustificacionIndustria4)} required={!requiereJustificacionIndustria4 ? undefined : 'required'} />
                    {/if}
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label labelFor="justificacion_economia_naranja" value="¿El proyecto está relacionado con la economía naranja?" />
                </div>
                <div>
                    <div class="flex items-center mb-14">
                        <Switch bind:checked={requiereJustificacionEconomiaNaranja} />
                    </div>
                    {#if requiereJustificacionEconomiaNaranja}
                        <InfoMessage class="mb-2" message="Si el proyecto está relacionado con la economía naranja por favor realice la justificación. (Ver documento de apoyo: Guía Rápida SENA es NARANJA.)" />
                        <Textarea
                            label="Justificación"
                            maxlength="40000"
                            id="justificacion_economia_naranja"
                            error={errors.justificacion_economia_naranja}
                            bind:value={$formJustificacionEconomiaNaranja.justificacion_economia_naranja}
                            on:input={() => syncColumnLong('justificacion_economia_naranja', $formJustificacionEconomiaNaranja)}
                            required={!requiereJustificacionEconomiaNaranja ? undefined : 'required'}
                        />
                    {/if}
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label labelFor="justificacion_politica_discapacidad" value="¿El proyecto aporta a la Política Institucional para Atención de las Personas con discapacidad?" />
                </div>
                <div>
                    <div class="flex items-center mb-14">
                        <Switch bind:checked={requiereJustificacionPoliticaDiscapacidad} />
                    </div>
                    {#if requiereJustificacionPoliticaDiscapacidad}
                        <InfoMessage class="mb-2" message="Si el proyecto aporta a la Política Institucional para Atención de las Personas con discapacidad por favor realice la justificación. RESOLUCIÓN 01726 DE 2014 - Por la cual se adopta la Política Institucional para Atención de las Personas con discapacidad." />
                        <Textarea
                            label="Justificación"
                            maxlength="40000"
                            id="justificacion_politica_discapacidad"
                            error={errors.justificacion_politica_discapacidad}
                            bind:value={$formJustificacionPoliticaDiscapacidad.justificacion_politica_discapacidad}
                            on:input={() => syncColumnLong('justificacion_politica_discapacidad', $formJustificacionPoliticaDiscapacidad)}
                            required={!requiereJustificacionPoliticaDiscapacidad ? undefined : 'required'}
                        />
                    {/if}
                </div>
            </div>

            <hr class="mt-5 mb-5" />

            <div>
                <p class="text-center mt-36 mb-8">¿Cuál es el origen de las muestras con las que se realizarán las actividades de investigación, bioprospección y/o aprovechamiento comercial o industrial?</p>
                <InfoMessage class="mb-2" message="Nota: Bioprospección se define como la exploración sistemática y sostenible de la biodiversidad para identificar y obtener nuevas fuentes de compuestos químicos, genes, proteínas, microorganismos y otros productos que tienen potencial de ser aprovechados comercialmente" />
                <InputError message={errors.muestreo} />
                <div class="flex mt-20 items-center">
                    <FormField>
                        <Radio bind:group={$form.muestreo} value="1" />
                        <span slot="label">
                            Especies Nativas. (es la especie o subespecie taxonómica o variedad de animales cuya área de disposición geográfica se extiende al territorio nacional o a aguas jurisdiccionales colombianas o forma parte de los mismos comprendidas las especies o subespecies que migran temporalmente a ellos, siempre y cuando no se encuentren en el país o migren a él como resultado
                            voluntario o involuntario de la actividad humana. Pueden ser silvestre, domesticada o escapada de domesticación incluyendo virus, viroides y similares)
                        </span>
                    </FormField>
                </div>

                <!-- Si seleccionan Especies nativas -->
                {#if $form.muestreo == 1}
                    <InfoMessage class="mb-2" classes="mt-10" message="Ha seleccionado Especies Nativas. Por favor responda las siguientes preguntas:" />
                    <div class="flex mb-20">
                        <div class="bg-gray-200 flex-1 p-8">
                            <div class="flex items-center">
                                <Label required class="mb-4" id="1.1" value="¿Qué actividad pretende realizar con la especie nativa?" />
                            </div>

                            <p class="bg-indigo-100 mt-10 p-4 text-indigo-600">Seleccione una opción</p>
                            <div class="flex mt-4 items-center">
                                <FormField>
                                    <Radio bind:group={$form.actividades_muestreo} value="1.1.1" />
                                    <span slot="label"> Separación de las unidades funcionales y no funcionales del ADN y el ARN, en todas las formas que se encuentran en la naturaleza. </span>
                                </FormField>
                            </div>
                            <div class="flex mt-4 items-center">
                                <FormField>
                                    <Radio bind:group={$form.actividades_muestreo} value="1.1.2" />
                                    <span slot="label"> Aislamiento de una o varias moléculas, entendidas estas como micro y macromoléculas, producidas por el metabolismo de un organismo. </span>
                                </FormField>
                            </div>
                            <div class="flex mt-4 items-center">
                                <FormField>
                                    <Radio bind:group={$form.actividades_muestreo} value="1.1.3" />
                                    <span slot="label"> Solicitar patente sobre una función o propiedad identificada de una molécula, que se ha aislado y purificado. </span>
                                </FormField>
                            </div>
                            <div class="flex mt-4 items-center">
                                <FormField>
                                    <Radio bind:group={$form.actividades_muestreo} value="1.1.4" />
                                    <span slot="label"> No logro identificar la actividad a desarrollar con la especie nativa </span>
                                </FormField>
                            </div>
                        </div>

                        <div class="bg-gray-300 flex-1 p-8">
                            <div class="flex items-center">
                                <Label required class="mb-4" id="1.2" value="¿Cuál es la finalidad de las actividades a realizar con la especie nativa/endémica?" />
                            </div>

                            <p class="bg-indigo-100 mt-10 p-4 text-indigo-600">Seleccione una opción</p>
                            <div class="flex mt-4 items-center">
                                <FormField>
                                    <Radio bind:group={$form.objetivo_muestreo} value="1.2.1" />
                                    <span slot="label"> Investigación básica sin fines comerciales </span>
                                </FormField>
                            </div>
                            <div class="flex mt-4 items-center">
                                <FormField>
                                    <Radio bind:group={$form.objetivo_muestreo} value="1.2.2" />
                                    <span slot="label"> Bioprospección en cualquiera de sus fases </span>
                                </FormField>
                            </div>
                            <div class="flex mt-4 items-center">
                                <FormField>
                                    <Radio bind:group={$form.objetivo_muestreo} value="1.2.3" />
                                    <span slot="label"> Comercial o Industrial </span>
                                </FormField>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="flex mt-4 items-center">
                    <FormField>
                        <Radio bind:group={$form.muestreo} value="2" />
                        <span slot="label"> Especies Introducidas. (son aquellas que no son nativas de Colombia y que ingresaron al país por intervención humana) </span>
                    </FormField>
                </div>
                <div class="flex mt-4 items-center">
                    <FormField>
                        <Radio bind:group={$form.muestreo} value="3" />
                        <span slot="label"> Recursos genéticos humanos y sus productos derivados </span>
                    </FormField>
                </div>
                <div class="flex mt-4 items-center">
                    <FormField>
                        <Radio bind:group={$form.muestreo} value="4" />
                        <span slot="label"> Intercambio de recursos genéticos y sus productos derivados, recursos biológicos que los contienen o los componentes asociados a estos. (son aquellas que realizan las comunidades indígenas, afroamericanas y locales de los Países Miembros de la Comunidad Andina entre sí y para su propio consumo, basadas en sus prácticas consuetudinarias) </span>
                    </FormField>
                </div>
                <div class="flex mt-4 items-center">
                    <FormField>
                        <Radio bind:group={$form.muestreo} value="5" />
                        <span slot="label">
                            Recurso biológico que involucren actividades de sistemática molecular, ecología molecular, evolución y biogeografía molecular (siempre que el recurso biológico se haya colectado en el marco de un permiso de recolección de especímenes de especies silvestres de la diversidad biológica con fines de investigación científica no comercial o provenga de una colección
                            registrada ante el Instituto Alexander van Humboldt)
                        </span>
                    </FormField>
                </div>
                <div class="flex mt-4 items-center">
                    <FormField>
                        <Radio bind:group={$form.muestreo} value="6" />
                        <span slot="label"> No aplica </span>
                    </FormField>
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label
                        required
                        class="mb-4"
                        labelFor="recoleccion_especimenes"
                        value="En la ejecución del proyecto se requiere la recolección de especímenes de especies silvestres de la diversidad biológica con fines de elaboración de estudios ambientales (entendiendo como recolección los procesos de remoción o extracción temporal o definitiva de una especie ya sea vegetal o animal del medio natural) Nota: este permiso no se requiere cuando las actividades de recolección se limiten a investigaciones científicas o con fines industriales, comerciales o de prospección biológica."
                    />
                </div>
                <div>
                    <Select items={opcionesSiNo} id="recoleccion_especimenes" bind:selectedValue={$form.recoleccion_especimenes} error={errors.recoleccion_especimenes} autocomplete="off" placeholder="Seleccione una opción" required />
                </div>
                <div />
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="relacionado_plan_tecnologico" value="¿El proyecto se alinea con el plan tecnológico desarrollado por el centro de formación?" />
                </div>
                <div>
                    <Select items={opcionesIDiDropdown} id="relacionado_plan_tecnologico" bind:selectedValue={$form.relacionado_plan_tecnologico} error={errors.relacionado_plan_tecnologico} autocomplete="off" placeholder="Seleccione una opción" required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="relacionado_agendas_competitividad" value="¿El proyecto se alinea con las Agendas Departamentales de Competitividad e Innovación?" />
                </div>
                <div>
                    <Select items={opcionesIDiDropdown} id="relacionado_agendas_competitividad" bind:selectedValue={$form.relacionado_agendas_competitividad} error={errors.relacionado_agendas_competitividad} autocomplete="off" placeholder="Seleccione una opción" required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="relacionado_mesas_sectoriales" value="¿El proyecto se alinea con las Mesas Sectoriales?" />
                </div>
                <div>
                    <Select items={opcionesIDiDropdown} id="relacionado_mesas_sectoriales" bind:selectedValue={$form.relacionado_mesas_sectoriales} error={errors.relacionado_mesas_sectoriales} autocomplete="off" placeholder="Seleccione una opción" required />
                </div>
            </div>
            {#if $form.relacionado_mesas_sectoriales?.value == 1}
                {#if isSuperAdmin || idi.proyecto.modificable == true}
                    <div class="bg-indigo-100 p-5 mt-10">
                        <InputError message={errors.mesa_sectorial_id} />
                        <div class="grid grid-cols-2">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5" style="transform: translateX(-50px);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-indigo-600">Por favor seleccione la o las mesas sectoriales con la cual o las cuales se alinea el proyecto</p>
                            </div>
                            <div class="bg-white grid grid-cols-2 max-w-xl overflow-y-scroll shadow-2xl mt-4 h-80">
                                {#each mesasSectoriales as { id, nombre }, i}
                                    <FormField>
                                        <Checkbox bind:group={$form.mesa_sectorial_id} value={id} />
                                        <span slot="label">{nombre}</span>
                                    </FormField>
                                {/each}
                            </div>
                        </div>
                    </div>
                {:else}
                    <div class="grid grid-cols-2">
                        <div>Mesas sectoriales relacionadas:</div>
                        <div>
                            <ul class="list-disc p-4">
                                {#each mesasSectoriales as { id, nombre }, i}
                                    {#each $form.mesa_sectorial_id as mesaSectorialRelacionada}
                                        {#if id == mesaSectorialRelacionada}
                                            <li>{nombre}</li>
                                        {/if}
                                    {/each}
                                {/each}
                            </ul>
                        </div>
                    </div>
                {/if}
            {/if}

            <div class="mt-40 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="relacionado_tecnoacademia" value="¿El proyecto se formuló en conjunto con la tecnoacademia?" />
                </div>
                <div>
                    <Select items={opcionesIDiDropdown} id="relacionado_tecnoacademia" bind:selectedValue={$form.relacionado_tecnoacademia} error={errors.relacionado_tecnoacademia} autocomplete="off" placeholder="Seleccione una opción" required />
                </div>
            </div>

            {#if $form.relacionado_tecnoacademia?.value == 1}
                {#if isSuperAdmin || idi.proyecto.modificable == true}
                    <div class="bg-indigo-100 p-5 mt-10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5" style="transform: translateX(-50px);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <div class="grid grid-cols-2">
                            <div>
                                <p class="text-indigo-600">Por favor seleccione la Tecnoacademia con la cual articuló el proyecto</p>
                            </div>
                            <div>
                                <Select items={tecnoacademias} id="tecnoacademia_id" bind:selectedValue={$form.tecnoacademia_id} error={errors.tecnoacademia_id} autocomplete="off" placeholder="Seleccione una opción" required />
                                {#if lineasTecnologicas?.length > 0}
                                    <div class="bg-white grid grid-cols-2 max-w-xl overflow-y-scroll shadow-2xl mt-4 h-80">
                                        {#each lineasTecnologicas as { value, label }, i}
                                            <Label class="p-3 border-t border-b flex items-center text-sm" labelFor={'linea-tecnologica-' + value} value={label} />

                                            <div class="border-b border-t flex items-center justify-center">
                                                <input type="checkbox" bind:group={$form.linea_tecnologica_id} id={'linea-tecnologica-' + value} {value} class="rounded text-indigo-500" />
                                            </div>
                                        {/each}
                                    </div>
                                {:else}
                                    <div>
                                        <p>Parece que no se han encontrado elementos, por favor haga clic en <strong>Refrescar</strong></p>
                                        <button on:click={getLineasTecnologicas} type="button" class="flex underline">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Refrescar
                                        </button>
                                    </div>
                                {/if}
                            </div>
                        </div>
                        <InputError message={errors.linea_tecnologica_id} />
                    </div>
                {:else}
                    <div class="grid grid-cols-2">
                        <div>Tecnoacademia relacionada</div>
                        <div>
                            <Select items={tecnoacademias} id="tecnoacademia_id" bind:selectedValue={$form.tecnoacademia_id} error={errors.tecnoacademia_id} autocomplete="off" placeholder="Seleccione una opción" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div>Líneas tecnológicas relacionadas:</div>
                        <div>
                            <ul class="list-disc p-4">
                                {#each lineasTecnologicas as { value, label }, i}
                                    {#each $form.linea_tecnologica_id as lineaTecnologica}
                                        {#if value == lineaTecnologica}
                                            <li>{label}</li>
                                        {/if}
                                    {/each}
                                {/each}
                            </ul>
                        </div>
                    </div>
                {/if}
            {/if}

            <div class="mt-40 grid grid-cols-1">
                <div>
                    <Label required class="mb-4" labelFor="resumen" value="Resumen del proyecto" />
                    <InfoMessage class="mb-2" message="Información necesaria para darle al lector una idea precisa de la pertinencia y calidad del proyecto. Explique en qué consiste el problema o necesidad, cómo cree que lo resolverá, cuáles son las razones que justifican su ejecución y las herramientas que se utilizarán en el desarrollo del proyecto." />
                </div>
                <div>
                    <Textarea label="Resumen" maxlength="40000" id="resumen" error={errors.resumen} on:input={() => syncColumnLong('resumen', $resumenForm)} bind:value={$resumenForm.resumen} required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-1">
                <div>
                    <Label required class="mb-4" labelFor="antecedentes" value="Antecedentes" />
                    <InfoMessage class="mb-2" message="Presenta las investigaciones, innovaciones o desarrollos tecnológicos que se han realizado a nivel internacional, nacional, departamental o municipal en el marco de la temática de la propuesta del proyecto; que muestran la pertinencia del proyecto, citar toda la información consignada utilizando normas APA última edición." />
                </div>
                <div>
                    <Textarea label="Antecedentes" maxlength="40000" id="antecedentes" error={errors.antecedentes} bind:value={$formAntecedentes.antecedentes} on:input={() => syncColumnLong('antecedentes', $formAntecedentes)} required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-1">
                <div>
                    <Label required class="mb-4" labelFor="marco_conceptual" value="Marco conceptual" />
                    <InfoMessage class="mb-2" message="Descripción de los aspectos conceptuales y/o teóricos relacionados con el problema. Se hace la claridad que no es un listado de definiciones." />
                </div>
                <div>
                    <Textarea label="Marco conceptual" maxlength="20000" id="marco_conceptual" error={errors.marco_conceptual} bind:value={$formMarcoConceptual.marco_conceptual} on:input={() => syncColumnLong('marco_conceptual', $formMarcoConceptual)} required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="numero_aprendices" value="Número de los aprendices que se beneficiarán en la ejecución del proyecto" />
                </div>
                <div>
                    <Input label="Número de aprendices" id="numero_aprendices" type="number" input$min="0" input$max="9999" class="mt-1" error={errors.numero_aprendices} placeholder="Escriba el número de aprendices que se beneficiarán en la ejecución del proyecto" bind:value={$form.numero_aprendices} required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="municipios" value="Nombre de los municipios beneficiados" />
                </div>
                <div>
                    <SelectMulti id="municipios" bind:selectedValue={$form.municipios} items={municipios} isMulti={true} error={errors.municipios} placeholder="Buscar municipios" required />
                    {#if municipios?.length == 0}
                        <div>
                            <p>Parece que no se han encontrado elementos, por favor haga clic en <strong>Refrescar</strong></p>
                            <button on:click={getMunicipios} type="button" class="flex underline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refrescar
                            </button>
                        </div>
                    {/if}
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label required class="mb-4" labelFor="programas_formacion" value="Nombre de los programas de formación con registro calificado a impactar" />
                </div>
                <div>
                    <SelectMulti id="programas_formacion" bind:selectedValue={$form.programas_formacion} items={programasFormacion} isMulti={true} error={errors.programas_formacion} placeholder="Buscar por el nombre del programa de formación" required />
                    {#if programasFormacion?.length == 0}
                        <div>
                            <p>Parece que no se han encontrado elementos, por favor haga clic en <strong>Refrescar</strong></p>
                            <button on:click={getProgramasFormacion} type="button" class="flex underline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refrescar
                            </button>
                        </div>
                    {/if}
                </div>
            </div>

            <div class="mt-44 grid grid-cols-2">
                <div>
                    <Label class="mb-4" labelFor="programas_formacion_articulados" value="Nombre de los programas de formación articulados" />
                </div>
                <div>
                    <SelectMulti id="programas_formacion_articulados" bind:selectedValue={$form.programas_formacion_articulados} items={programasFormacionArticular} isMulti={true} error={errors.programas_formacion_articulados} placeholder="Buscar por el nombre del programa de formación" />
                    {#if programasFormacionArticular?.length == 0}
                        <div>
                            <p>Parece que no se han encontrado elementos, por favor haga clic en <strong>Refrescar</strong></p>
                            <button on:click={getProgramasFormacionArticular} type="button" class="flex underline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refrescar
                            </button>
                        </div>
                    {/if}
                </div>
            </div>

            <div class="mt-44 grid grid-cols-1">
                <div>
                    <Label required class="mb-4" labelFor="impacto_municipios" value="Descripción del beneficio en los municipios" />
                </div>
                <div>
                    <Textarea label="Descripción" maxlength="40000" id="impacto_municipios" error={errors.impacto_municipios} bind:value={$formImpactoMunicipios.impacto_municipios} on:input={() => syncColumnLong('impacto_municipios', $formImpactoMunicipios)} required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-1">
                <div>
                    <Label required class="mb-4" labelFor="impacto_centro_formacion" value="Impacto en el centro de formación" />
                </div>
                <div>
                    <Textarea label="Descripción" maxlength="40000" id="impacto_centro_formacion" error={errors.impacto_centro_formacion} bind:value={$formImpactoCentroFormacion.impacto_centro_formacion} on:input={() => syncColumnLong('impacto_centro_formacion', $formImpactoCentroFormacion)} required />
                </div>
            </div>

            <div class="mt-44 grid grid-cols-1">
                <div>
                    <Label required class="mb-4" labelFor="bibliografia" value="Bibliografía" />
                    <InfoMessage class="mb-2" message="Lista de las referencias utilizadas en cada apartado del proyecto. Utilizar normas APA- Última edición (http://biblioteca.sena.edu.co/images/PDF/InstructivoAPA.pdf)." />
                </div>
                <div>
                    <Textarea label="Bibliografía" maxlength="40000" id="bibliografia" error={errors.bibliografia} bind:value={$formBibliografia.bibliografia} on:input={() => syncColumnLong('bibliografia', $formBibliografia)} required />
                </div>
            </div>
        </fieldset>
        <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center justify-between sticky bottom-0">
            {#if isSuperAdmin || (checkPermission(authUser, [3, 4]) && idi.proyecto.modificable == true && convocatoria.fase == 1)}
                <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar </button>
            {/if}
            {#if isSuperAdmin || (checkPermission(authUser, [3, 4]) && idi.proyecto.modificable == true)}
                <small>{idi.updated_at}</small>

                <LoadingButton loading={sending} class="btn-indigo" type="submit">Guardar</LoadingButton>
            {/if}
        </div>
    </form>

    <Dialog bind:open={proyectoDialogOpen} id="informacion">
        <div slot="title" class="flex items-center flex-col mt-4">
            <figure>
                <img src={window.basePath + '/images/proyecto.png'} alt="Proyecto" class="h-32 mb-6" />
            </figure>
            Código del proyecto: {idi.proyecto.codigo}

            {#if versiones.length > 0}
                <hr class="my-10 w-full border-white" />
                <h6>Versiones - PDF</h6>
                <ul>
                    {#each versiones as version, i}
                        <li class="bg-white p-2 rounded text-black text-xs mb-2">
                            {#if version.estado == 1}
                                <a href={route('convocatorias.proyectos.version', [convocatoria.id, idi.proyecto.id, version.version])}>Descargar versión {version.created_at}</a>
                            {:else}
                                Generando una nueva versión, regrese pronto.
                            {/if}
                        </li>
                    {/each}
                </ul>
                <hr class="my-10 w-full border-white" />
            {:else}
                <hr class="my-10 w-full border-white" />

                <h6>Versiones - PDF</h6>

                <p class="text-xs">No se ha generado un PDF aún</p>
                <hr class="my-10 w-full border-white" />
            {/if}
        </div>
        <div slot="content">
            <div>
                <h1 class="text-center mt-4 mb-4">Para terminar el numeral de <strong>Generalidades</strong> por favor continue diligenciando los siguientes campos:</h1>
                <p class="text-center mb-4">Si ya están completos omita esta información.</p>
                <ul class="list-disc">
                    <li>Video</li>
                    <li>Industria 4.0</li>
                    <li>Economía naranja</li>
                    <li>Política Institucional para Atención de las Personas con discapacidad</li>
                    <li>Origen de las muestras</li>
                    <li>Plan tecnológico</li>
                    <li>Agendas Departamentales de Competitividad e Innovación</li>
                    <li>Mesas sectoriales</li>
                    <li>Tecnoacademia</li>
                    <li>Resumen</li>
                    <li>Antecedentes</li>
                    <li>Marco conceptual</li>
                    <li>Bibliografía</li>
                    <li>Número de aprendices beneficiados</li>
                    <li>Nombre de los municipios beneficiados</li>
                    <li>Impacto en el centro de formación</li>
                </ul>
            </div>
        </div>
        <div slot="actions">
            <div class="p-4">
                <Button on:click={(event) => (proyectoDialogOpen = false)} variant={null}>Omitir</Button>
                {#if idi.proyecto.modificable}
                    <Button variant="raised" on:click={(event) => (proyectoDialogOpen = false)} on:click={() => Inertia.visit('#tematica_estrategica_id')}>Continuar diligenciando</Button>
                {/if}
            </div>
        </div>
    </Dialog>

    <Dialog bind:open={dialogOpen}>
        <div slot="titulo" class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Eliminar recurso
        </div>
        <div slot="content">
            <InfoMessage class="mb-2" message="¿Está seguro (a) que desea eliminar este proyecto?<br />Una vez eliminado el proyecto, todos sus recursos y datos se eliminarán de forma permanente." />

            <form on:submit|preventDefault={destroy} id="delete-idi" class="mt-10 mb-28">
                <Label labelFor="password" value="Ingrese su contraseña para confirmar que desea eliminar permanentemente este proyecto" class="mb-4" />
                <Password id="password" class="w-full" bind:value={$deleteForm.password} error={errors.password} required autocomplete="current-password" />
            </form>
        </div>
        <div slot="actions">
            <div class="p-4">
                <Button on:click={(event) => (dialogOpen = false)} variant={null}>Cancelar</Button>
                <Button variant="raised" form="delete-idi">Confirmar</Button>
            </div>
        </div>
    </Dialog>
</AuthenticatedLayout>
