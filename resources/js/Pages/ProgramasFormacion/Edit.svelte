<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import Dialog from '@/Shared/Dialog'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Button from '@/Shared/Button'
    import Select from '@/Shared/Select'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'

    export let errors
    export let programaFormacion
    export let modalidades
    export let nivelesFormacion

    $: $title = programaFormacion ? programaFormacion.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        nombre: programaFormacion.nombre,
        codigo: programaFormacion.codigo,
        modalidad: {
            value: programaFormacion.modalidad,
            label: modalidades.find((item) => item.value == programaFormacion.modalidad)?.label,
        },
        nivel_formacion: {
            value: programaFormacion.nivel_formacion,
            label: nivelesFormacion.find((item) => item.value == programaFormacion.nivel_formacion)?.label,
        },
        registro_calificado: programaFormacion.registro_calificado,
        centro_formacion_id: programaFormacion.centro_formacion_id,
    })

    function submit() {
        if (isSuperAdmin || checkRole(authUser, [4])) {
            $form.put(route('programas-formacion.update', programaFormacion.id), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin) {
            $form.delete(route('programas-formacion.destroy', programaFormacion.id))
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin || checkRole(authUser, [4])}
                        <a use:inertia href={route('programas-formacion.index')} class="text-indigo-400 hover:text-indigo-600"> Programas de formación </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {programaFormacion.nombre}
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || checkRole(authUser, [4]) ? undefined : true}>
                <div class="mt-4">
                    <Input label="Nombre" id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required />
                </div>

                <div class="mt-4">
                    <Input label="Código" id="codigo" type="number" input$min="0" input$max="2147483647" class="mt-1" bind:value={$form.codigo} error={errors.codigo} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="modalidad" value="Modalidad de estudio" />
                    <Select id="modalidad" items={modalidades} bind:selectedValue={$form.modalidad} error={errors.modalidad} autocomplete="off" placeholder="Seleccione una modalidad de estudio" required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="nivel_formacion" value="Nivel de formación" />
                    <Select id="nivel_formacion" items={nivelesFormacion} bind:selectedValue={$form.nivel_formacion} error={errors.nivel_formacion} autocomplete="off" placeholder="Seleccione un nivel de formación" required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="centro_formacion_id" value="Centro de formación" />
                    <DynamicList id="centro_formacion_id" bind:value={$form.centro_formacion_id} routeWebApi={route('web-api.centros-formacion')} placeholder="Busque por el nombre del centro de formación" message={errors.centro_formacion_id} required />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar programa de formación </button>
                {/if}
                {#if isSuperAdmin || checkRole(authUser, [4])}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar programa de formación</LoadingButton>
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
