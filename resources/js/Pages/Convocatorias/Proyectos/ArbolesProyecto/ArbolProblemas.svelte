<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { useForm, page } from '@inertiajs/inertia-svelte'
    import { Inertia } from '@inertiajs/inertia'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { onMount } from 'svelte'
    import { _ } from 'svelte-i18n'

    import Dialog from '@/Shared/Dialog'
    import Button from '@/Shared/Button'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Textarea from '@/Shared/Textarea'
    import InfoMessage from '@/Shared/InfoMessage'
    import Stepper from '@/Shared/Stepper'
    import Dropdown from '@/Shared/Dropdown'
    import Icon from '@/Shared/Icon'

    import { createPopper } from '@popperjs/core'

    export let errors
    export let to_pdf
    export let convocatoria
    export let proyecto
    export let efectosDirectos
    export let causasDirectas

    let formId
    let dialogTitle
    let codigo
    let sending = false
    let dialogOpen = false

    let cantidadCeldasCausasIndirectas = 3
    let cantidadCeldasEfectosIndirectos = 3

    $: $title = 'Árbol de problemas'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    /**
     * Efectos indirectos
     */
    let formEfectoIndirecto = useForm({
        id: 0,
        efecto_directo_id: 0,
        descripcion: '',
    })

    let showEfectoIndirectoForm = false
    function showEfectoIndirectoDialog(efectoIndirecto, efectoDirectoId) {
        reset()
        codigo = efectoIndirecto?.id != null ? 'EFE-' + efectoIndirecto.efecto_directo_id + '-IND-' + efectoIndirecto.id : ''
        dialogTitle = 'Efecto indirecto'
        formId = 'efecto-indirecto'
        showEfectoIndirectoForm = true
        dialogOpen = true

        if (efectoIndirecto != null) {
            $formEfectoIndirecto.id = efectoIndirecto.id
            $formEfectoIndirecto.descripcion = efectoIndirecto.descripcion
            $formEfectoIndirecto.efecto_directo_id = efectoIndirecto.efecto_directo_id
        } else {
            $formEfectoIndirecto.id = null
            $formEfectoIndirecto.descripcion = null
            $formEfectoIndirecto.efecto_directo_id = efectoDirectoId
        }
    }

    function submitEfectoIndirecto() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formEfectoIndirecto.post(
                route('proyectos.efecto-indirecto', {
                    proyecto: proyecto.id,
                    efecto_directo: $formEfectoIndirecto.efecto_directo_id,
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

    function destroyEfectoIndirecto(efectoIndirecto) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.efecto-indirecto.destroy', [proyecto.id, efectoIndirecto.id]), [], { preserveScroll: true })
        }
    }

    /**
     * Efectos directos
     */
    let formEfectoDirecto = useForm({
        id: 0,
        descripcion: '',
    })

    let showEfectoDirectoForm = false
    function showEfectoDirectoDialog(efectoDirecto) {
        reset()
        codigo = 'EFE-' + efectoDirecto.id
        dialogTitle = 'Efecto directo'
        formId = 'efecto-directo'
        showEfectoDirectoForm = true
        dialogOpen = true
        $formEfectoDirecto.descripcion = efectoDirecto.descripcion
        $formEfectoDirecto.id = efectoDirecto.id
    }

    function submitEfectoDirecto() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formEfectoDirecto.post(
                route('proyectos.efecto-directo', {
                    proyecto: proyecto.id,
                    efecto_directo: $formEfectoDirecto.id,
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

    function destroyEfectoDirecto(efectoDirecto) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.efecto-directo.destroy', [proyecto.id, efectoDirecto.id]), [], { preserveScroll: true })
        }
    }

    /**
     * Problema central
     */
    let formProblemaCentral = useForm({
        identificacion_problema: proyecto.identificacion_problema,
        problema_central: proyecto.problema_central,
        justificacion_problema: proyecto.justificacion_problema,
        pregunta_formulacion_problema: proyecto.pregunta_formulacion_problema,
    })

    let showProblemaCentralForm = false
    function showProblemaCentralDialog() {
        reset()
        dialogTitle = 'Problema central'
        formId = 'problema-central'
        showProblemaCentralForm = true
        dialogOpen = true
        $formProblemaCentral.identificacion_problema = proyecto.identificacion_problema
        $formProblemaCentral.problema_central = proyecto.problema_central
        $formProblemaCentral.justificacion_problema = proyecto.justificacion_problema
        $formProblemaCentral.pregunta_formulacion_problema = proyecto.pregunta_formulacion_problema
    }

    function submitProblemaCentral() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formProblemaCentral.post(route('proyectos.problema-central', proyecto.id), {
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
     * Causas directas
     */
    let formCausaDirecta = useForm({
        id: 0,
        descripcion: '',
    })

    let showCausaDirectaForm = false
    function showCausaDirectaDialog(causaDirecta) {
        reset()
        codigo = 'CAU-' + causaDirecta.id
        dialogTitle = 'Causa directa'
        formId = 'causa-directa'
        showCausaDirectaForm = true
        dialogOpen = true
        $formCausaDirecta.id = causaDirecta.id
        $formCausaDirecta.descripcion = causaDirecta.descripcion
    }

    function submitCausaDirecta() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formCausaDirecta.post(
                route('proyectos.causa-directa', {
                    proyecto: proyecto.id,
                    causa_directa: $formCausaDirecta.id,
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

    function destroyCausaDirecta(causaDirecta) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.causa-directa.destroy', [proyecto.id, causaDirecta.id]), [], { preserveScroll: true })
        }
    }

    /**
     * Causas indirectas
     */
    let formCausaIndirecta = useForm({
        id: 0,
        causa_directa_id: 0,
        descripcion: '',
    })

    let showCausaIndirectaForm = false
    function showCausaIndirectaDialog(causaIndirecta, causaDirectaId) {
        reset()
        codigo = causaIndirecta?.id != null ? 'CAU-' + causaIndirecta.causa_directa_id + '-IND-' + causaIndirecta.id : ''
        dialogTitle = 'Causa indirecta'
        formId = 'causa-indirecta'
        showCausaIndirectaForm = true
        dialogOpen = true
        if (causaIndirecta != null) {
            $formCausaIndirecta.id = causaIndirecta.id
            $formCausaIndirecta.descripcion = causaIndirecta.descripcion
            $formCausaIndirecta.causa_directa_id = causaIndirecta.causa_directa_id
        } else {
            $formCausaIndirecta.id = null
            $formCausaIndirecta.descripcion = null
            $formCausaIndirecta.causa_directa_id = causaDirectaId
        }
    }

    function submitCausaIndirecta() {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            $formCausaIndirecta.post(
                route('proyectos.causa-indirecta', {
                    proyecto: proyecto.id,
                    causa_directa: $formCausaIndirecta.causa_directa_id,
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

    function destroyCausaIndirecta(causaIndirecta) {
        if (isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true)) {
            Inertia.post(route('proyectos.causa-indirecta.destroy', [proyecto.id, causaIndirecta.id]), [], { preserveScroll: true })
        }
    }

    function reset() {
        showEfectoIndirectoForm = false
        showEfectoDirectoForm = false
        showProblemaCentralForm = false
        showCausaDirectaForm = false
        showCausaIndirectaForm = false
        dialogTitle = ''
        codigo = ''
        formId = ''

        $formCausaIndirecta.reset()
        $formCausaDirecta.reset()
        $formProblemaCentral.reset()
        $formEfectoDirecto.reset()
        $formEfectoIndirecto.reset()
    }

    function closeDialog() {
        reset()
        dialogOpen = false
    }

    onMount(() => {
        const efectoIndirecto = document.querySelector('#efecto-indirecto-tooltip-placement')
        const efectoIndirectoTooltip = document.querySelector('#efecto-indirecto-tooltip')
        const arrowEfectoIndirecto = document.querySelector('#arrow-efecto-indirecto')

        const efectoDirecto = document.querySelector('#efecto-directo-tooltip-placement')
        const efectoDirectoTooltip = document.querySelector('#efecto-directo-tooltip')
        const arrowEfectoDirecto = document.querySelector('#arrow-efecto-directo')

        const problemaCentral = document.querySelector('#problema-central-tooltip-placement')
        const problemaCentralTooltip = document.querySelector('#problema-central-tooltip')
        const arrowProblemaCentral = document.querySelector('#arrow-problema-central')

        const causaDirecta = document.querySelector('#causa-directa-tooltip-placement')
        const causaDirectaTooltip = document.querySelector('#causa-directa-tooltip')
        const arrowDirectCause = document.querySelector('#arrow-causa-directa')

        const causaIndirecta = document.querySelector('#causa-indirecta-tooltip-placement')
        const causaIndirectaTooltip = document.querySelector('#causa-indirecta-tooltip')
        const arrowCausaIndirecta = document.querySelector('#arrow-causa-indirecta')

        let tooltips = [
            {
                element: {
                    target: efectoIndirecto,
                    tooltip: efectoIndirectoTooltip,
                    arrow: arrowEfectoIndirecto,
                },
            },
            {
                element: {
                    target: efectoDirecto,
                    tooltip: efectoDirectoTooltip,
                    arrow: arrowEfectoDirecto,
                },
            },
            {
                element: {
                    target: problemaCentral,
                    tooltip: problemaCentralTooltip,
                    arrow: arrowProblemaCentral,
                },
            },
            {
                element: {
                    target: causaDirecta,
                    tooltip: causaDirectaTooltip,
                    arrow: arrowDirectCause,
                },
            },
            {
                element: {
                    target: causaIndirecta,
                    tooltip: causaIndirectaTooltip,
                    arrow: arrowCausaIndirecta,
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

        causasDirectasCount = containerArbol.getElementsByClassName('causa-directa-container').length
    })
    let causasDirectasCount
    let containerArbol
    let containerCausaDirecta
</script>

<AuthenticatedLayout>
    {#if !to_pdf}
        <Stepper {convocatoria} {proyecto} />
    {/if}

    <h1 class="text-3xl {to_pdf ? '' : 'mt-24'} mb-8 text-center">Árbol de problemas</h1>
    <p class="text-center">Diligenciar el árbol de problemas iniciando con el problema principal (tronco), sus causas (raíces) y efectos (ramas).</p>

    <div class="mt-16 relative" bind:this={containerArbol}>
        <div class="flex opacity-50 absolute" style="height: {containerArbol?.offsetHeight}px; top: 0;">
            {#each { length: causasDirectasCount } as _empty, i}
                <div class={i % 2 == 0 ? 'bg-indigo-100' : 'bg-indigo-200'} style="width: {containerCausaDirecta?.offsetWidth}px;" />
            {/each}
        </div>
        <!-- Efectos -->
        <div class="flex mb-14" style={proyecto?.codigo_linea_programatica == 69 ? 'margin-left: -15px; margin-right: -15px;' : ''}>
            {#each efectosDirectos as efectoDirecto, i}
                <div class="flex-1{proyecto?.codigo_linea_programatica == 70 && efectoDirecto.efectos_indirectos.length == 0 ? ' flex items-end' : ''}">
                    {#if i == 0}
                        <!-- Efectos indirectos -->
                        <div id="efecto-indirecto-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small>Efectos <br /> indirectos</small>
                            <div id="arrow-efecto-indirecto" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <div class="flex mb-14" id={i == 0 ? 'efecto-indirecto-tooltip-placement' : ''} aria-describedby={i == 0 ? 'tooltip' : ''}>
                        {#each efectoDirecto.efectos_indirectos as efectoIndirecto}
                            <div class="flex-1 efectos-directos relative">
                                <div
                                    on:click={showEfectoIndirectoDialog(efectoIndirecto, efectoDirecto.id)}
                                    class="{proyecto?.codigo_linea_programatica == 69
                                        ? (efectoIndirecto.descripcion != null) & (i < 3) || (efectoIndirecto.descripcion != null && i > 5 && i < 9)
                                            ? 'bg-indigo-300 hover:bg-indigo-400 '
                                            : 'bg-indigo-500 hover:bg-indigo-600 '
                                        : efectoIndirecto.descripcion != null && i % 2 == 0
                                        ? 'bg-indigo-300 hover:bg-indigo-400 '
                                        : efectoIndirecto.descripcion == null && i % 2 == 0
                                        ? 'bg-gray-300 hover:bg-gray-400 '
                                        : efectoIndirecto.descripcion != null && i % 2 != 0
                                        ? 'bg-indigo-500 hover:bg-indigo-600 '
                                        : 'bg-gray-400 hover:bg-gray-500 '}h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                                >
                                    <p class="paragraph-ellipsis text-xs text-white line-height-1-24">
                                        <small class="title block font-bold mb-2">EFE-{efectoDirecto.id}-IND-{efectoIndirecto.id}</small>
                                        {#if efectoIndirecto.descripcion != null && efectoIndirecto.descripcion.length > 0}
                                            {efectoIndirecto.descripcion}
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
                                        <Button variant={null} on:click={destroyEfectoIndirecto(efectoIndirecto)}>
                                            Eliminar
                                            <br />
                                            <small class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                También se eliminará el impacto asociado
                                            </small>
                                        </Button>
                                    </div>
                                </Dropdown>
                            </div>
                        {/each}
                        {#each { length: cantidadCeldasEfectosIndirectos - efectoDirecto.efectos_indirectos.length } as _empty}
                            <div class="flex-1 efectos-directos relative" on:click={showEfectoIndirectoDialog(null, efectoDirecto.id)}>
                                <div class="{proyecto?.codigo_linea_programatica == 69 ? 'bg-gray-300 hover:bg-gray-400 ' : i % 2 == 0 ? 'bg-gray-300 hover:bg-gray-400 ' : 'bg-gray-400 hover:bg-gray-500 '}h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5">
                                    <p class="text-sm text-white line-height-1-24" />
                                </div>
                            </div>
                        {/each}
                    </div>

                    {#if i == 0}
                        <!-- Efecto directo -->
                        <div id="efecto-directo-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small>Efectos <br /> directos</small>
                            <div id="arrow-efecto-directo" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <!-- Efecto directo -->
                    <div class="efectos-directos relative flex-1" id={i == 0 ? 'efecto-directo-tooltip-placement' : ''} aria-describedby={i == 0 ? 'tooltip' : ''}>
                        <div
                            on:click={showEfectoDirectoDialog(efectoDirecto)}
                            class="{proyecto?.codigo_linea_programatica == 69
                                ? (efectoDirecto.descripcion != null) & (i < 3) || (efectoDirecto.descripcion != null && i > 5 && i < 9)
                                    ? 'bg-indigo-300 hover:bg-indigo-400 '
                                    : 'bg-indigo-500 hover:bg-indigo-600 '
                                : efectoDirecto.descripcion != null && i % 2 == 0
                                ? 'bg-indigo-300 hover:bg-indigo-400 '
                                : efectoDirecto.descripcion == null && i % 2 == 0
                                ? 'bg-gray-300 hover:bg-gray-400 '
                                : efectoDirecto.descripcion != null && i % 2 != 0
                                ? 'bg-indigo-500 hover:bg-indigo-600 '
                                : 'bg-gray-400 hover:bg-gray-500 '}h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                        >
                            <p class="paragraph-ellipsis text-white text-sm line-height-1-24">
                                <small class="title block font-bold mb-2">EFE-{efectoDirecto.id}</small>
                                {#if efectoDirecto.descripcion != null && efectoDirecto.descripcion.length > 0}
                                    {efectoDirecto.descripcion}
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
                                <Button variant={null} on:click={destroyEfectoDirecto(efectoDirecto)}>
                                    Eliminar
                                    <br />
                                    <small class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        También se eliminará el resultado asociado
                                    </small>
                                </Button>
                            </div>
                        </Dropdown>
                    </div>
                </div>
            {/each}
        </div>

        <!-- Problema central -->
        <div id="problema-central-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
            <small class="block">Problema <br /> central</small>
            <div id="arrow-problema-central" class="arrow" data-popper-arrow />
        </div>
        <div class="problema-central relative" id="problema-central-tooltip-placement" aria-describedby="tooltip">
            <div on:click={showProblemaCentralDialog} class="h-36 {proyecto.problema_central != null ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-indigo-300 hover:bg-indigo-400'} rounded shadow-lg cursor-pointer mr-1.5 p-2.5">
                {#if proyecto.problema_central != null && proyecto.problema_central.length > 0}
                    <p class="paragraph-ellipsis text-white text-sm line-height-1-24">
                        {proyecto.problema_central}
                    </p>
                {/if}
            </div>
        </div>

        <!-- Causas -->
        <div class="flex mt-14">
            {#each causasDirectas as causaDirecta, i}
                <div class="flex-1">
                    <!-- Causa directa -->
                    {#if i == 0}
                        <div id="causa-directa-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small>Causas directas</small>
                            <div id="arrow-causa-directa" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <div bind:this={containerCausaDirecta} class="causas-directas-line causa-directa-container relative flex-1" id={i == 0 ? 'causa-directa-tooltip-placement' : ''} aria-describedby={i == 0 ? 'tooltip' : ''}>
                        <div
                            on:click={showCausaDirectaDialog(causaDirecta)}
                            class="{causaDirecta.descripcion != null && i % 2 == 0 ? 'bg-indigo-300 hover:bg-indigo-400' : causaDirecta.descripcion == null && i % 2 == 0 ? 'bg-gray-300 hover:bg-gray-400' : causaDirecta.descripcion != null && i % 2 != 0 ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-gray-400 hover:bg-gray-500'} h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                        >
                            <p class="paragraph-ellipsis text-white text-sm line-height-1-24">
                                <small class="title block font-bold mb-2">CAU-{causaDirecta.id}</small>
                                {#if causaDirecta.descripcion != null && causaDirecta.descripcion.length > 0}
                                    {causaDirecta.descripcion}
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
                                <Button variant={null} on:click={destroyCausaDirecta(causaDirecta)}>
                                    Eliminar
                                    <br />
                                    <small class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        También se eliminará el objetivo específico asociado
                                    </small>
                                </Button>
                            </div>
                        </Dropdown>
                    </div>

                    {#if i == 0}
                        <div id="causa-indirecta-tooltip" class="tooltip bg-black" role="tooltip" data-popper-placement="left">
                            <small>Causas indirectas</small>
                            <div id="arrow-causa-indirecta" class="arrow" data-popper-arrow />
                        </div>
                    {/if}
                    <!-- Causas indirectas -->
                    <div class="flex flex-wrap causas-directas-line relative mt-14" id={i == 0 ? 'causa-indirecta-tooltip-placement' : ''} aria-describedby={i == 0 ? 'tooltip' : ''}>
                        {#each causaDirecta.causas_indirectas as causaIndirecta}
                            <div class="mb-4 relative" style="flex: 1 0 33.333%">
                                <div
                                    on:click={showCausaIndirectaDialog(causaIndirecta, causaDirecta.id)}
                                    class="{causaIndirecta.descripcion != null && i % 2 == 0 ? 'bg-indigo-300 hover:bg-indigo-400' : causaIndirecta.descripcion == null && i % 2 == 0 ? 'bg-gray-300 hover:bg-gray-400' : causaIndirecta.descripcion != null && i % 2 != 0 ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-gray-400 hover:bg-gray-500'} h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5"
                                >
                                    <p class="paragraph-ellipsis text-white text-xs line-height-1-24">
                                        <small class="title block font-bold mb-2">CAU-{causaDirecta.id}-IND-{causaIndirecta.id}</small>
                                        {#if causaIndirecta.descripcion != null && causaIndirecta.descripcion.length > 0}
                                            {causaIndirecta.descripcion}
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
                                        <Button variant={null} on:click={destroyCausaIndirecta(causaIndirecta)}>
                                            Eliminar
                                            <br />
                                            <small class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                También se eliminará la actividad asociada
                                            </small>
                                        </Button>
                                    </div>
                                </Dropdown>
                            </div>
                        {/each}

                        {#each { length: cantidadCeldasCausasIndirectas - causaDirecta.causas_indirectas.length } as _empty}
                            <div class="mb-4 relative" style="flex: 1 0 33.333%">
                                <div on:click={showCausaIndirectaDialog(null, causaDirecta.id)} class="{i % 2 == 0 ? 'bg-gray-300 hover:bg-gray-400' : 'bg-gray-400 hover:bg-gray-500'} h-36 rounded shadow-lg cursor-pointer mr-1.5 p-2.5">
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
    <Dialog bind:open={dialogOpen} id="arbol-problemas">
        <div slot="title" class="mb-10 text-center">
            <div class="text-primary">
                {dialogTitle}
            </div>
            {#if codigo}
                <small class="block text-primary-light">
                    Código: {codigo}
                </small>
            {/if}
        </div>
        <div slot="content">
            {#if showCausaIndirectaForm}
                <form on:submit|preventDefault={submitCausaIndirecta} id="causa-indirecta">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <div class="mt-4">
                            <InfoMessage class="mb-2" message="Son acciones o hechos que dan origen a las causas directas y que se encuentran a partir del segundo nivel, justamente debajo de las causas directas del árbol de problemas." />
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="40000" id="causa-indirecta-descripcion" error={errors.descripcion} bind:value={$formCausaIndirecta.descripcion} required />
                        </div>
                    </fieldset>
                </form>
            {:else if showCausaDirectaForm}
                <form on:submit|preventDefault={submitCausaDirecta} id="causa-directa">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <div class="mt-4">
                            <InfoMessage class="mb-2" message="Son las acciones o hechos concretos que generan o dan origen al problema central. Aparecen en la estructura del árbol en el primer nivel, inmediatamente abajo del problema central." />
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="40000" id="causa-directa-descripcion" error={errors.descripcion} bind:value={$formCausaDirecta.descripcion} required />
                        </div>
                    </fieldset>
                </form>
            {:else if showEfectoIndirectoForm}
                <form on:submit|preventDefault={submitEfectoIndirecto} id="efecto-indirecto">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <div class="mt-4">
                            <InfoMessage class="mb-2" message="Corresponden a situaciones negativas generadas por los efectos directos." />
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="40000" id="efecto-directo-descripcion" error={errors.descripcion} bind:value={$formEfectoIndirecto.descripcion} required />
                        </div>
                    </fieldset>
                </form>
            {:else if showProblemaCentralForm}
                <form on:submit|preventDefault={submitProblemaCentral} id="problema-central">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <div class="mt-10">
                            <Label required class="mb-4" labelFor="identificacion_problema" value="Identificación y descripción del problema" />
                            <InfoMessage
                                class="mb-2"
                                message="1. Descripción de la necesidad, problema u oportunidad identificada del plan tecnológico y/o agendas departamentales de innovación y competitividad.<br>2. Descripción del problema que se atiende con el proyecto, sustentado en el contexto, la caracterización, los datos, las estadísticas, de la regional, entre otros, citar toda la información consignada utilizando normas APA última edición. La información debe ser de fuentes primarias de información, ejemplo: Secretarías, DANE, Artículos científicos, entre otros."
                            />
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Identificación y descripción del problema" maxlength="40000" id="identificacion_problema" error={errors.identificacion_problema} bind:value={$formProblemaCentral.identificacion_problema} required />
                        </div>
                        <div class="mt-10">
                            <Label required class="mb-4" labelFor="justificacion_problema" value="Justificación" />
                            <InfoMessage class="mb-2" message="Descripción de la solución al problema (descrito anteriormente) que se presenta en la regional, así como las consideraciones que justifican la elección del proyecto. De igual forma, describir la pertinencia y viabilidad del proyecto en el marco del impacto regional identificado en el instrumento de planeación." />

                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Justificación del problema" maxlength="5000" id="justificacion_problema" error={errors.justificacion_problema} bind:value={$formProblemaCentral.justificacion_problema} required />
                        </div>

                        <div class="mt-10">
                            <Label required class="mb-4" labelFor="problema_central" value="Problema central (tronco)" />
                            <InfoMessage
                                class="mb-2"
                                message="Para la redacción del problema central se debe tener en cuenta: a) Se debe referir a una situación existente, teniendo en cuenta la mayoría de los siguientes componentes: social, económico, tecnológico, ambiental. b) Su redacción debe ser una oración corta con sujeto, verbo y predicado. c) Se debe comprender con total claridad; el problema se debe formular mediante una oración clara y sin ambigüedades."
                            />
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Problema central" maxlength="5000" id="problema_central" error={errors.problema_central} bind:value={$formProblemaCentral.problema_central} required />
                        </div>
                    </fieldset>
                </form>
            {:else if showEfectoDirectoForm}
                <form on:submit|preventDefault={submitEfectoDirecto} id="efecto-directo">
                    <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true) ? undefined : true}>
                        <div class="mt-4">
                            <InfoMessage class="mb-2" message="Son aquellos que caracterizan las consecuencias de la situación que existirá en caso de no ejecutarse el proyecto; es decir, si se mantiene inalterado el orden actual de las cosas." />
                            <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} label="Descripción" maxlength="40000" id="efecto-directo-descripcion" error={errors.descripcion} bind:value={$formEfectoDirecto.descripcion} required />
                        </div>
                    </fieldset>
                </form>
            {/if}
        </div>
        <div slot="actions" class="block flex w-full">
            <Button on:click={closeDialog} type="button" variant={null}>Cancelar</Button>
            {#if (isSuperAdmin && formId) || (checkPermission(authUser, [1, 3, 4]) && proyecto.modificable == true && formId)}
                <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit" form={formId}>Guardar</LoadingButton>
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
    .efectos-directos.relative.flex-1:before {
        content: '';
        bottom: -57px;
        position: absolute;
        right: 50%;
        width: 2px;
        height: 57px;
        background: #d2d6ff;
    }

    .causas-directas-line.relative:before {
        content: '';
        position: absolute;
        top: -55px;
        height: 55px;
        right: 50%;
        width: 2px;
        background: #d2d6ff;
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

    .line-height-1-24 {
        line-height: 1.24;
    }

    :global(#arbol-problemas-dialog .mdc-dialog__surface) {
        width: 750px;
        max-width: calc(100vw - 32px) !important;
    }

    :global(#arbol-problemas-dialog .mdc-dialog__content) {
        padding-top: 40px !important;
    }

    :global(#arbol-problemas-dialog .mdc-dialog__title) {
        border-bottom: 1px solid rgba(0, 0, 0, 0.12);
        margin-bottom: 0;
    }
</style>
