<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { useForm, page } from '@inertiajs/inertia-svelte'
    import { Inertia } from '@inertiajs/inertia'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { onMount } from 'svelte'
    import { _ } from 'svelte-i18n'
    import { createPopper } from '@popperjs/core'

    import Label from '@/Shared/Label'
    import Input from '@/Shared/Input'
    import LoadingButton from '@/Shared/LoadingButton'
    import Textarea from '@/Shared/Textarea'
    import Select from '@/Shared/Select'
    import InfoMessage from '@/Shared/InfoMessage'
    import Dialog from '@/Shared/Dialog'
    import Button from '@/Shared/Button'
    import Stepper from '@/Shared/Stepper'
    import Dropdown from '@/Shared/Dropdown'

    export let errors
    export let to_pdf
    export let convocatoria
    export let proyecto
    export let efectosDirectos
    export let causasDirectas
    export let tiposImpacto
    export let tipoProyectoA
    export let resultados

    let cantidadCeldasActividades = 3
    let cantidadCeldasImpactos = 3

    let formId
    let sending = false
    let dialogOpen = false
    let dialogTitle
    let codigo

    $: $title = 'Árbol de objetivos'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    /**
     * Mensaje para ítems bloqueados
     */
    let generalInfoType = 0
    let showGeneralInfo = false
    function showGeneralInfoDialog(tipo) {
        reset()
        dialogTitle = 'Información general'
        dialogOpen = true

        generalInfoType = tipo
        showGeneralInfo = true
    }

    /**
     * Impactos
     */
    let formImpacto = useForm({
        id: 0,
        efecto_indirecto_id: 0,
        descripcion: '',
        tipo: '',
        resultado_id: '',
    })

    let showImpactoForm = false
    let impactoEfectoIndirecto
    function showImpactDialog(efectoIndirecto, efectoIndirectoId, resultadoId) {
        // reset()
        codigo = efectoIndirecto.impacto.id != null ? 'RES-' + resultadoId + '-IMP-' + efectoIndirecto.impacto.id : ''
        dialogTitle = 'Impacto'
        dialogOpen = true
        showImpactoForm = true
        formId = 'impacto-form'
        impactoEfectoIndirecto = efectoIndirecto.descripcion

        if (efectoIndirecto.impacto != null) {
            $formImpacto.id = efectoIndirecto.impacto.id
            $formImpacto.descripcion = efectoIndirecto.impacto.descripcion
            $formImpacto.tipo = {
                value: efectoIndirecto.impacto.tipo,
                label: tiposImpacto.find((item) => item.value == efectoIndirecto.impacto.tipo)?.label,
            }
            $formImpacto.efecto_indirecto_id = efectoIndirecto.impacto.efectoIndirectoId
            $formImpacto.resultado_id = resultadoId
        } else {
            $formImpacto.id = null
            $formImpacto.efecto_indirecto_id = efectoIndirectoId
            $formImpacto.resultado_id = ''
        }
    }

    function submitImpacto() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formImpacto.post(
                route('proyectos.impacto', {
                    proyecto: proyecto.id,
                    impacto: $formImpacto.id,
                }),
                {
                    onStart: () => {
                        sending = true
                    },
                    onSuccess: () => {
                        closeDialog()
                    },
                    onFinish: () => {
                        sending = false
                    },
                    preserveScroll: true,
                },
            )
        }
    }

    function destroyImpacto(impacto) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.impacto.destroy', [proyecto.id, impacto.id]), [], { preserveScroll: true })
        }
    }

    /**
     * Resultados
     */
    let formResultado = useForm({
        descripcion: '',
        trl: '',
    })

    let showResultadoForm = false
    let descripcionObjetivoEspecifico = []
    let resultadoEfectoDirecto
    $: causasDirectas
    function showResultadoDialog(efectoDirecto, resultado) {
        reset()
        let objetivoEspecifico = causasDirectas.find((causaDirecta) => causaDirecta.objetivo_especifico.id == resultado.objetivo_especifico_id)
        descripcionObjetivoEspecifico = {
            descripcion: objetivoEspecifico.objetivo_especifico?.descripcion ? objetivoEspecifico.objetivo_especifico?.descripcion : 'Sin información registrada',
            numero: objetivoEspecifico.objetivo_especifico?.numero,
        }
        codigo = 'RES-' + resultado.id
        dialogTitle = 'Resultado'
        dialogOpen = true
        showResultadoForm = true
        formId = 'resultado-form'
        $formResultado.id = resultado.id
        $formResultado.descripcion = resultado.descripcion
        $formResultado.trl = resultado.trl
        resultadoEfectoDirecto = efectoDirecto.descripcion ?? 'Sin información registrada'
    }

    function submitResult() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formResultado.post(
                route('proyectos.resultado', {
                    proyecto: proyecto.id,
                    resultado: $formResultado.id,
                }),
                {
                    onStart: () => {
                        sending = true
                    },
                    onSuccess: () => {
                        closeDialog()
                    },
                    onFinish: () => {
                        sending = false
                    },
                    preserveScroll: true,
                },
            )
        }
    }

    function destroyResultado(resultado) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.resultado.destroy', [proyecto.id, resultado.id]), [], { preserveScroll: true })
        }
    }

    /**
     * Objetivo general
     */
    let formObjetivoGeneral = useForm({
        objetivo_general: proyecto.objetivo_general,
    })

    let showObjetivoGeneralForm = false
    let problemaCentral
    function showObjetivoGeneralDialog() {
        reset()
        dialogTitle = 'Objetivo general'
        dialogOpen = true
        showObjetivoGeneralForm = true
        formId = 'objetivo-general-form'
        problemaCentral = proyecto.problema_central
        $formObjetivoGeneral.objetivo_general = proyecto.objetivo_general
    }

    function submitObjetivoGeneral() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formObjetivoGeneral.post(route('proyectos.objetivo-general', proyecto.id), {
                onStart: () => {
                    sending = true
                },
                onSuccess: () => {
                    closeDialog()
                },
                onFinish: () => {
                    sending = false
                },
                preserveScroll: true,
            })
        }
    }

    /**
     * Objetivos específicos
     */
    let formObjetivoEspecifico = useForm({
        id: 0,
        descripcion: '',
        numero: 0,
    })

    let showObjetivoEspecificoForm = false
    let causaDirectaObjetivoEspecifico
    function showObjetivoEspecificoDialog(causaDirecta, numero) {
        reset()
        codigo = 'OBJ-ESP-' + causaDirecta.objetivo_especifico.id
        dialogTitle = causaDirecta.objetivo_especifico.numero
        dialogOpen = true
        showObjetivoEspecificoForm = true
        formId = 'objetivo-especifico-form'
        $formObjetivoEspecifico.id = causaDirecta.objetivo_especifico.id
        $formObjetivoEspecifico.descripcion = causaDirecta.objetivo_especifico.descripcion
        $formObjetivoEspecifico.numero = numero
        causaDirectaObjetivoEspecifico = causaDirecta.descripcion ?? 'Sin información registrada'
    }

    function submitObjetivoEspecifico() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formObjetivoEspecifico.post(
                route('proyectos.objetivo-especifico', {
                    proyecto: proyecto.id,
                    objetivo_especifico: $formObjetivoEspecifico.id,
                }),
                {
                    onStart: () => {
                        sending = true
                    },
                    onSuccess: () => {
                        closeDialog()
                    },
                    onFinish: () => {
                        sending = false
                    },
                    preserveScroll: true,
                },
            )
        }
    }

    function destroyObjetivoEspecifico(objetivoEspecifico) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.objetivo-especifico.destroy', [proyecto.id, objetivoEspecifico.id]), [], { preserveScroll: true })
        }
    }

    /**
     * Actividades
     */
    let formActividad = useForm({
        id: 0,
        causa_indirecta_id: 0,
        objetivo_especifico_id: 0,
        resultado_id: 0,
        descripcion: '',
    })

    let showActividadForm = false
    let actividadCausaIndirecta
    let resultadosFiltrados
    function showActivityDialog(causaIndirecta, objetivoEspecifico) {
        reset()
        codigo = causaIndirecta.actividad.id != null ? 'OBJ-ESP-' + objetivoEspecifico + '-ACT-' + causaIndirecta.actividad.id : ''
        dialogTitle = 'Actividad'
        dialogOpen = true
        showActividadForm = true
        formId = 'actividad-form'
        $formActividad.id = causaIndirecta.actividad.id
        $formActividad.causa_indirecta_id = causaIndirecta.actividad.causa_indirecta_id
        $formActividad.objetivo_especifico_id = objetivoEspecifico
        $formActividad.descripcion = causaIndirecta.actividad.descripcion
        $formActividad.resultado_id = {
            value: causaIndirecta.actividad.resultado_id,
            label: resultados.find((item) => item.value == causaIndirecta.actividad.resultado_id)?.label,
        }
        actividadCausaIndirecta = causaIndirecta.descripcion ?? 'Sin información registrada'
        resultadosFiltrados = resultados.filter((item) => item.objetivo_especifico_id == objetivoEspecifico)
        resultadosFiltrados = resultadosFiltrados.filter((item) => item.label != null)
    }

    // typeof resultadosFiltrados.find((item) => item.label == null) == 'object'

    function submitActividad() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            if ((typeof resultadosFiltrados.find((item) => item.label == null) == 'object') == false) {
                $formActividad.post(
                    route('proyectos.actividad', {
                        convocatoria: convocatoria.id,
                        proyecto: proyecto.id,
                        actividad: $formActividad.id,
                    }),
                    {
                        onStart: () => {
                            sending = true
                        },
                        onSuccess: () => {
                            closeDialog()
                        },
                        onFinish: () => {
                            sending = false
                        },
                        preserveScroll: true,
                    },
                )
            }
        }
    }

    function destroyActividad(actividad) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.actividad.destroy', [proyecto.id, actividad.id]), [], { preserveScroll: true })
        }
    }

    function reset() {
        showObjetivoGeneralForm = false
        showActividadForm = false
        showResultadoForm = false
        showImpactoForm = false
        showGeneralInfo = false
        showObjetivoEspecificoForm = false
        dialogTitle = ''
        codigo = ''
        formId = ''

        $formImpacto.reset()
        $formResultado.reset()
        $formObjetivoGeneral.reset()
        $formObjetivoEspecifico.reset()
        $formActividad.reset()
    }

    function closeDialog() {
        reset()
        dialogOpen = false
    }

    let objetivosCount
    let containerArbol
    let containerObjetivo
    onMount(() => {
        const impacto = document.querySelector('#impacto-tooltip-placement')
        const impactoTooltip = document.querySelector('#impacto-tooltip')
        const arrowImpact = document.querySelector('#arrow-impacto')

        const resultado = document.querySelector('#resultado-tooltip-placement')
        const resultadoTooltip = document.querySelector('#resultado-tooltip')
        const arrowResultado = document.querySelector('#arrow-resultado')

        const objetivoGeneral = document.querySelector('#objetivo-general-tooltip-placement')
        const objetivoGeneralTooltip = document.querySelector('#objetivo-general-tooltip')
        const arrowObjetivoGeneral = document.querySelector('#arrow-objetivo-general')

        const objetivoEspecifico = document.querySelector('#objetivo-especifico-tooltip-placement')
        const objetivoEspecificoTooltip = document.querySelector('#objetivo-especifico-tooltip')
        const arrowObjetivoEspecifico = document.querySelector('#arrow-objetivo-especifico')

        const actividad = document.querySelector('#actividad-tooltip-placement')
        const actividadTooltip = document.querySelector('#actividad-tooltip')
        const arrowActividad = document.querySelector('#arrow-actividad')

        let tooltips = [
            {
                element: {
                    target: impacto,
                    tooltip: impactoTooltip,
                    arrow: arrowImpact,
                },
            },
            {
                element: {
                    target: resultado,
                    tooltip: resultadoTooltip,
                    arrow: arrowResultado,
                },
            },
            {
                element: {
                    target: objetivoGeneral,
                    tooltip: objetivoGeneralTooltip,
                    arrow: arrowObjetivoGeneral,
                },
            },
            {
                element: {
                    target: objetivoEspecifico,
                    tooltip: objetivoEspecificoTooltip,
                    arrow: arrowObjetivoEspecifico,
                },
            },
            {
                element: {
                    target: actividad,
                    tooltip: actividadTooltip,
                    arrow: arrowActividad,
                },
            },
        ]

        tooltips.map(function (tooltip) {
            createPopper(tooltip.element.target, tooltip.element.tooltip, {
                placement: 'left',
                modifiers: [
                    {
                        name: 'arrow',
                        options: {
                            element: tooltip.element.arrow,
                        },
                    },
                ],
            })
        })
        objetivosCount = containerArbol.getElementsByClassName('objetivo-container').length
    })
</script>

<AuthenticatedLayout>
    {#if !to_pdf}
        <Stepper {convocatoria} {proyecto} />
    {/if}

    <h1 class="text-3xl {to_pdf ? '' : 'mt-24'} mb-8 text-center">Árbol de objetivos</h1>
    <p class="text-center">El árbol de objetivos se obtiene al transformar en positivo el árbol de problemas manteniendo la misma estructura y niveles de jerarquía.</p>

    <div class="mt-16 relative" bind:this={containerArbol}>
        <div class="flex opacity-50 absolute" style="height: {containerArbol?.offsetHeight}px; top: 0;">
            {#each { length: objetivosCount } as _empty, i}
                <div class={i % 2 == 0 ? 'bg-red-100' : 'bg-red-200'} style="width: {containerObjetivo?.offsetWidth}px;" />
            {/each}
        </div>
        <div class="flex mb-14">
            {#each efectosDirectos as efectoDirecto, i}
                <div class="flex-1">
                    <!-- Impactos -->
                    {#if i == 0}
                        <div id="impacto-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small>Impactos</small>
                            <div id="arrow-impacto" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <div class="flex mb-14" id={i == 0 ? 'impacto-tooltip-placement' : ''} aria-describedby={i == 0 ? 'tooltip' : ''}>
                        {#each efectoDirecto.efectos_indirectos as efectoIndirecto}
                            <div class="flex-1 resultados relative">
                                <div
                                    on:click={showImpactDialog(efectoIndirecto, efectoIndirecto.id, efectoDirecto.resultados[0].id)}
                                    class="{proyecto?.codigo_linea_programatica == 69
                                        ? (efectoIndirecto.descripcion != null) & (i < 3) || (efectoIndirecto.descripcion != null && i > 5 && i < 9)
                                            ? 'bg-orangered-400 hover:bg-orangered-500 '
                                            : 'bg-orangered-500 hover:bg-orangered-600 '
                                        : efectoIndirecto.descripcion != null && i % 2 == 0
                                        ? 'bg-orangered-400 hover:bg-orangered-500 '
                                        : efectoIndirecto.descripcion == null && i % 2 == 0
                                        ? 'bg-gray-300 hover:bg-gray-400 '
                                        : efectoIndirecto.descripcion != null && i % 2 != 0
                                        ? 'bg-orangered-500 hover:bg-orangered-600 '
                                        : 'bg-gray-400 hover:bg-gray-500 '}tree-label h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                                >
                                    <p class="paragraph-ellipsis text-xs node text-white line-height-1-24">
                                        {#if efectoIndirecto.impacto}
                                            <small class="title block font-bold mb-2">
                                                RES{#each efectoDirecto.resultados as { id }}
                                                    -{id}
                                                {/each}
                                                -IMP-
                                                {efectoIndirecto.impacto.id}
                                            </small>
                                            {#if efectoIndirecto.impacto.descripcion != null && efectoIndirecto.impacto.descripcion.length > 0}
                                                {efectoIndirecto.impacto.descripcion}
                                            {/if}
                                        {/if}
                                    </p>
                                </div>
                                <Dropdown class="absolute bottom-1.5" placement="bottom-end">
                                    <div class="flex items-center cursor-pointer select-none group">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <div slot="dropdown" class="mt-2 py-2 shadow-xl bg-white rounded text-sm">
                                        <Button variant={null} on:click={destroyImpacto(efectoIndirecto.impacto)}>Eliminar</Button>
                                    </div>
                                </Dropdown>
                            </div>
                        {/each}
                        {#each { length: cantidadCeldasImpactos - efectoDirecto.efectos_indirectos.length } as _empty}
                            <div on:click={() => showGeneralInfoDialog(1)} class="flex-1 resultados relative">
                                <div class="h-36 bg-gray-300 rounded shadow-lg hover:bg-gray-400 cursor-pointer mr-1.5 p-2.5">
                                    <p class="paragraph-ellipsis text-sm text-white line-height-1-24" />
                                </div>
                            </div>
                        {/each}
                    </div>
                    <!-- Resultado -->
                    {#if i == 0}
                        <div id="resultado-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small>Resultados</small>
                            <div id="arrow-resultado" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <div
                        class="{i == 0 ? 'resultados' : (proyecto?.codigo_linea_programatica == 68 && i == 1) || (proyecto?.codigo_linea_programatica == 68 && i == 2) ? 'resultados-line' : proyecto?.codigo_linea_programatica == 68 && i == 3 ? 'resultados-line-4' : 'resultados'} relative flex-1 flex flex-wrap"
                        id={i == 0 ? 'resultado-tooltip-placement' : ''}
                        aria-describedby={i == 0 ? 'tooltip' : ''}
                    >
                        {#each efectoDirecto.resultados as resultado}
                            <div class="relative mb-4" style="flex: 1 0 33.333%">
                                <div
                                    class="{proyecto?.codigo_linea_programatica == 69
                                        ? (efectoDirecto.descripcion != null) & (i < 3) || (efectoDirecto.descripcion != null && i > 5 && i < 9)
                                            ? 'bg-orangered-400 hover:bg-orangered-500 '
                                            : 'bg-orangered-500 hover:bg-orangered-600 '
                                        : efectoDirecto.descripcion != null && i % 2 == 0
                                        ? 'bg-orangered-400 hover:bg-orangered-500 '
                                        : efectoDirecto.descripcion == null && i % 2 == 0
                                        ? 'bg-gray-300 hover:bg-gray-400 '
                                        : efectoDirecto.descripcion != null && i % 2 != 0
                                        ? 'bg-orangered-500 hover:bg-orangered-600 '
                                        : 'bg-gray-400 hover:bg-gray-500 '}tree-label h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                                    on:click={showResultadoDialog(efectoDirecto, resultado)}
                                >
                                    <div class="h-full">
                                        <p class="paragraph-ellipsis text-white text-sm line-height-1-24">
                                            <small class="title block font-bold mb-2">RES-{resultado.id}</small>
                                            {#if resultado.descripcion != null && resultado.descripcion.length > 0}
                                                {resultado.descripcion}
                                            {/if}
                                        </p>
                                    </div>
                                </div>
                                <Dropdown class="absolute bottom-1.5" placement="bottom-end">
                                    <div class="flex items-center cursor-pointer select-none group">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <div slot="dropdown" class="mt-2 py-2 shadow-xl bg-white rounded text-sm">
                                        <Button variant={null} on:click={destroyResultado(resultado)}>Eliminar</Button>
                                    </div>
                                </Dropdown>
                            </div>
                        {/each}
                    </div>
                </div>
            {/each}
        </div>

        <!-- Objetivo general -->
        <div id="objetivo-general-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
            <small>Objetivo general</small>
            <div id="arrow-objetivo-general" class="arrow" data-popper-arrow />
        </div>
        <div class="objetivo-general relative" id="objetivo-general-tooltip-placement" aria-describedby="tooltip">
            <div on:click={showObjetivoGeneralDialog} class="{proyecto.objetivo_general != null ? 'bg-orangered-400 hover:bg-orangered-500' : 'bg-gray-300 hover:bg-gray-400'} tree-label h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5">
                {#if proyecto.objetivo_general != null && proyecto.objetivo_general.length > 0}
                    <p class="paragraph-ellipsis text-white text-sm line-height-1-24">
                        {proyecto.objetivo_general}
                    </p>
                {/if}
            </div>
        </div>

        <div class="flex mt-14">
            {#each causasDirectas as causaDirecta, i}
                <div class="flex-1">
                    <!-- Objetivo específico -->
                    {#if i == 0}
                        <div id="objetivo-especifico-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small class="block line-height-1-24">
                                Objetivos <br /> específicos
                            </small>
                            <div id="arrow-objetivo-especifico" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <div bind:this={containerObjetivo} class="objetivo-especificos objetivo-container relative flex-1" id={i == 0 ? 'objetivo-especifico-tooltip-placement' : ''} aria-describedby={i == 0 ? 'tooltip' : ''}>
                        <div
                            on:click={showObjetivoEspecificoDialog(causaDirecta, i + 1)}
                            class="{causaDirecta.descripcion != null && i % 2 == 0 ? 'bg-orangered-400 hover:bg-orangered-500' : causaDirecta.descripcion == null && i % 2 == 0 ? 'bg-gray-300 hover:bg-gray-400' : causaDirecta.descripcion != null && i % 2 != 0 ? 'bg-orangered-500 hover:bg-orangered-600' : 'bg-gray-400 hover:bg-gray-500'} tree-label h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                        >
                            <p class="paragraph-ellipsis text-white text-sm line-height-1-24">
                                <small class="title block font-bold mb-2">
                                    OBJ-ESP-{causaDirecta.objetivo_especifico.id}
                                </small>
                                {#if causaDirecta.objetivo_especifico.descripcion != null && causaDirecta.objetivo_especifico.descripcion.length > 0}
                                    {causaDirecta.objetivo_especifico.descripcion}
                                {/if}
                            </p>
                        </div>
                        {#if causaDirecta.objetivo_especifico.descripcion != null && proyecto.modificable && proyecto?.codigo_linea_programatica != 70}
                            <Dropdown class="absolute bottom-1.5" placement="bottom-end">
                                <div class="flex items-center cursor-pointer select-none group">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <div slot="dropdown" class="mt-2 py-2 shadow-xl bg-white rounded text-sm">
                                    <Button variant={null} on:click={destroyObjetivoEspecifico(causaDirecta.objetivo_especifico)}>Eliminar</Button>
                                </div>
                            </Dropdown>
                        {/if}
                    </div>
                    <!-- Actividades -->
                    {#if i == 0}
                        <div id="actividad-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small>Actividades</small>
                            <div id="arrow-actividad" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <div class="flex flex-wrap objetivo-especificos relative mt-14" id={i == 0 ? 'actividad-tooltip-placement' : ''} aria-describedby={i == 0 ? 'tooltip' : ''}>
                        {#each causaDirecta.causas_indirectas as causaIndirecta}
                            <div class="mb-4 relative" style="flex: 1 0 33.333%">
                                <div
                                    on:click={showActivityDialog(causaIndirecta, causaDirecta.objetivo_especifico.id)}
                                    class="{causaIndirecta.descripcion != null && i % 2 == 0
                                        ? 'bg-orangered-400 hover:bg-orangered-500'
                                        : causaIndirecta.descripcion == null && i % 2 == 0
                                        ? 'bg-gray-300 hover:bg-gray-400'
                                        : causaIndirecta.descripcion != null && i % 2 != 0
                                        ? 'bg-orangered-500 hover:bg-orangered-600'
                                        : 'bg-gray-400 hover:bg-gray-500'} tree-label h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                                >
                                    <p class="paragraph-ellipsis text-xs node text-white line-height-1-24">
                                        {#if causaIndirecta.actividad}
                                            <small class="title block font-bold mb-2">
                                                OBJ-ESP-{causaDirecta.objetivo_especifico.id}-ACT-{causaIndirecta.actividad.id}
                                            </small>
                                            {#if causaIndirecta.actividad.descripcion != null && causaIndirecta.actividad.descripcion.length > 0}
                                                {causaIndirecta.actividad.descripcion}
                                            {/if}
                                        {/if}
                                    </p>
                                </div>
                                {#if causaIndirecta.actividad.descripcion != null && proyecto.modificable && proyecto?.codigo_linea_programatica != 70}
                                    <Dropdown class="absolute bottom-1.5" placement="bottom-end">
                                        <div class="flex items-center cursor-pointer select-none group">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </div>
                                        <div slot="dropdown" class="mt-2 py-2 shadow-xl bg-white rounded text-sm">
                                            <Button variant={null} on:click={destroyActividad(causaIndirecta.actividad)}>Eliminar</Button>
                                        </div>
                                    </Dropdown>
                                {/if}
                            </div>
                        {/each}
                        {#each { length: cantidadCeldasActividades - causaDirecta.causas_indirectas.length } as _empty, j}
                            <div id="{j}_empty_actividad" on:click={() => showGeneralInfoDialog(2)} class="mb-4" style="flex: 1 0 33.333%">
                                <div class="{i % 2 == 0 ? 'bg-gray-300 hover:bg-gray-400' : 'bg-gray-400 hover:bg-gray-500'} h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5">
                                    <p class="paragraph-ellipsis text-sm text-white line-height-1-24" />
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            {/each}
        </div>
    </div>

    <!-- Dialog -->
    <Dialog bind:open={dialogOpen} id="arbol-objetivos">
        <div slot="title">
            <div class="mb-10 text-center">
                <div class="text-primary">
                    {dialogTitle}
                </div>
                {#if codigo}
                    <small class="block text-primary-light">
                        Código: {codigo}
                    </small>
                {/if}
            </div>
        </div>
        <div slot="content">
            {#if showActividadForm}
                <InfoMessage class="mb-4">
                    Se debe evidenciar que la descripción de las actividades se realice de manera secuencial y de forma coherente con los productos a las cuales están asociadas para alcanzar el logro de cada uno de los objetivos específicos.
                    <br />
                    Las actividades deben redactarse en verbos en modo infinitivo, es decir, en palabras que expresen acciones y terminen en “ar”, “er” o “ir”, estos no deben hacer referencia a objetivos específicos o generales. Algunos ejemplos de verbos inadecuados para describir actividades son: apropiar, asegurar, colaborar, consolidar, desarrollar, fomentar, fortalecer, garantizar, implementar,
                    impulsar, mejorar, movilizar, proponer, promover, entre otros.
                </InfoMessage>
                <p class="block font-medium mb-2 text-gray-700 text-sm">Causa indirecta</p>
                <p class="mb-10 whitespace-pre-line">
                    {actividadCausaIndirecta}
                </p>
                <form on:submit|preventDefault={submitActividad} id="actividad-form">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <div>
                            <Label labelFor="resultado_id" value="Resultado" />
                            <Select id="resultado_id" items={resultadosFiltrados} bind:selectedValue={$formActividad.resultado_id} error={errors.resultado_id} autocomplete="off" placeholder="Seleccione un resultado" required />
                        </div>
                        <div class="mt-8">
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="15000" id="descripcion-actividad" error={errors.descripcion} bind:value={$formActividad.descripcion} required />
                        </div>
                    </fieldset>
                </form>
            {:else if showObjetivoEspecificoForm}
                {#if causaDirectaObjetivoEspecifico != 'Sin información registrada'}
                    <form on:submit|preventDefault={submitObjetivoEspecifico} id="objetivo-especifico-form">
                        <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                            <p class="block font-medium mb-2 text-gray-700 text-sm">Causa directa</p>

                            <p class="mb-20 whitespace-pre-line">
                                {causaDirectaObjetivoEspecifico}
                            </p>
                            <div>
                                <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="40000" id="descripcion-objetivo-especifico" error={errors.descripcion} bind:value={$formObjetivoEspecifico.descripcion} required />
                            </div>
                        </fieldset>
                    </form>
                {:else}
                    <InfoMessage class="mb-4" message="Debe generar primero la causa directa en el árbol de problemas" />
                {/if}
            {:else if showObjetivoGeneralForm}
                <form on:submit|preventDefault={submitObjetivoGeneral} id="objetivo-general-form">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <InfoMessage class="mb-2" message="Establece que pretende alcanzar la investigación. Se inicia con un verbo en modo infinitivo, es medible y alcanzable. Responde al Qué, Cómo y el Para qué" />
                        <p class="block font-medium mb-2 text-gray-700 text-sm">Problema central</p>

                        <p class="mb-20 whitespace-pre-line">
                            {problemaCentral ? problemaCentral : 'Sin información registrada'}
                        </p>
                        <div>
                            <Label required class="mb-4" labelFor="objetivo-general" value="Objetivo general" />
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="10000" id="objetivo-general" error={errors.objetivo_general} bind:value={$formObjetivoGeneral.objetivo_general} required />
                        </div>
                    </fieldset>
                </form>
            {:else if showResultadoForm}
                {#if resultadoEfectoDirecto != 'Sin información registrada'}
                    <InfoMessage class="mb-4">Se debe evidenciar que los resultados son directos, medibles y cuantificables que se alcanzarán con el desarrollo de cada uno de los objetivos específicos del proyecto.</InfoMessage>
                    <p class="block font-medium mb-2 text-gray-700 text-sm">Efecto directo</p>
                    <p class="mb-20 whitespace-pre-line">
                        {resultadoEfectoDirecto}
                    </p>

                    <p class="block font-medium mb-2 text-gray-700 text-sm">
                        {descripcionObjetivoEspecifico.numero}
                    </p>
                    <p class="mb-20 whitespace-pre-line">
                        {descripcionObjetivoEspecifico.descripcion}
                    </p>

                    <form on:submit|preventDefault={submitResult} id="resultado-form">
                        <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                            <div class="mb-10">
                                <Input label="TRL" id="trl" type="number" input$max="9" input$min="1" class="block w-full" error={errors.trl} bind:value={$formResultado.trl} required />
                            </div>
                            <div class="mb-20">
                                <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="1000" id="descripcion-resultado" error={errors.descripcion} bind:value={$formResultado.descripcion} required />
                            </div>
                        </fieldset>
                    </form>
                {:else}
                    <InfoMessage class="mb-4" message="Debe generar primero el efecto directo en el árbol de problemas" />
                {/if}
            {:else if showImpactoForm}
                <InfoMessage class="mb-4">Se busca medir la contribución potencial que genera el proyecto en los siguientes ámbitos: tecnológico, económico, ambiental, social, centro de formación, sector productivo</InfoMessage>

                <p class="block font-medium mb-2 text-gray-700 text-sm">Efecto indirecto</p>

                <p class="mt-4 whitespace-pre-line">
                    {impactoEfectoIndirecto}
                </p>

                <form on:submit|preventDefault={submitImpacto} id="impacto-form">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <div class="mt-4">
                            <Label labelFor="tipo-impacto" value="Tipo" />
                            <Select id="tipo-impacto" items={tiposImpacto} bind:selectedValue={$formImpacto.tipo} error={errors.tipo} autocomplete="off" placeholder="Seleccione un tipo" required />
                            {#if $formImpacto.tipo?.value == 4}
                                <InfoMessage
                                    message="Se busca minimizar y/o evitar los impactos negativos sobre el medio ambiente, tales como contaminación del aire, contaminación de corrientes de agua naturales, ruido, destrucción del paisaje, separación de comunidades que operan como unidades, etc. Por otro lado, se busca identificar diversas acciones de impacto ambiental positivo, tales como: producción limpia y sustentable, protección medioambiental, uso de residuos y reciclaje."
                                />
                            {:else if $formImpacto.tipo?.value == 2}
                                <InfoMessage
                                    message="Se busca medir la contribución potencial del proyecto en cualquiera de los siguientes ámbitos: generación y aplicación de nuevos conocimientos y tecnologías, desarrollo de infraestructura científico- tecnológica, articulación de diferentes proyectos para lograr un objetivo común, mejoramiento de la infraestructura, desarrollo de capacidades de gestión tecnológica."
                                />
                            {:else if $formImpacto.tipo?.value == 5}
                                <InfoMessage message="Se busca medir la contribución potencial del proyecto al desarrollo de la comunidad Sena (Aprendices, instructores y a la formación)" />
                            {:else if $formImpacto.tipo?.value == 6}
                                <InfoMessage message="Se busca medir la contribución potencial del proyecto al desarrollo del sector productivo en concordancia con el sector priorizado de Colombia Productiva y a la mesa técnica a la que pertenece el proyecto." />
                            {/if}
                        </div>
                        <div class="mt-4">
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="10000" id="descripcion-impacto" error={errors.descripcion} bind:value={$formImpacto.descripcion} required />
                        </div>
                    </fieldset>
                </form>
            {:else if showGeneralInfo}
                {#if generalInfoType == 1}
                    <p>Para poder editar este impacto, primero debe generar el efecto indirecto en el árbol de problemas.</p>
                {/if}
                {#if generalInfoType == 2}
                    <p class="mb-5">Para poder editar esta actividad, primero debe generar la causa indirecta en el árbol de problemas.</p>
                {/if}
            {/if}
        </div>
        <div slot="actions" class="block flex w-full">
            <Button on:click={closeDialog} type="button" variant={null}>Cancelar</Button>
            {#if (isSuperAdmin && formId) || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true && formId)}
                <LoadingButton loading={sending} class="btn-gray ml-auto" type="submit" form={formId}>Guardar</LoadingButton>
            {/if}
        </div>
    </Dialog>
</AuthenticatedLayout>

{#if to_pdf}
    <style>
        nav,
        button.absolute.bottom-1\.5,
        .bg-gray-200.p-4.rounded.border-orangered.border.mb-5 {
            display: none !important;
        }
    </style>
{/if}

<style>
    .resultados.relative.flex-1:before {
        content: '';
        bottom: -57px;
        position: absolute;
        right: 50%;
        width: 2px;
        height: 57px;
        background: #ff906e;
    }

    .resultados-line:before {
        content: '';
        bottom: -536px;
        position: absolute;
        right: 50%;
        width: 2px;
        height: 552px;
        background: #ff906e;
    }

    .resultados-line-4:before {
        content: '';
        bottom: -696px;
        position: absolute;
        right: 50%;
        width: 2px;
        height: 710px;
        background: #ff906e;
    }

    .objetivo-especificos.relative:before {
        content: '';
        top: -55px;
        position: absolute;
        right: 50%;
        width: 2px;
        height: 55px;
        background: #ff906e;
    }

    .line-height-1-24 {
        line-height: 1.2;
    }

    .tooltip {
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 13px;
        position: relative;
        z-index: 9999;
    }

    .arrow,
    .arrow::before {
        position: absolute;
        width: 8px;
        height: 8px;
        background: inherit;
    }

    .arrow {
        visibility: hidden;
    }

    .arrow::before {
        visibility: visible;
        content: '';
        transform: rotate(45deg);
    }

    .tooltip[data-popper-placement^='left'] > .arrow {
        right: -4px;
    }

    .node {
        line-height: 1.24;
    }

    :global(#arbol-objetivos-dialog .mdc-dialog__surface) {
        width: 750px;
        max-width: calc(100vw - 32px) !important;
    }

    :global(#arbol-objetivos-dialog .mdc-dialog__content) {
        padding-top: 40px !important;
    }

    :global(#arbol-objetivos-dialog .mdc-dialog__title) {
        border-bottom: 1px solid rgba(0, 0, 0, 0.12);
        margin-bottom: 0;
    }
</style>
