<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import Button from '@/Shared/Button'
    import LoadingButton from '@/Shared/LoadingButton'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'
    import Dialog from '@/Shared/Dialog'

    export let errors
    export let centroFormacion

    $: $title = centroFormacion ? centroFormacion.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        nombre: centroFormacion.nombre,
        codigo: centroFormacion.codigo,
        regional_id: centroFormacion.regional_id,
        subdirector_id: centroFormacion.subdirector_id,
        dinamizador_sennova_id: centroFormacion.dinamizador_sennova_id,
    })

    function submit() {
        if (isSuperAdmin) {
            $form.put(route('centros-formacion.update', centroFormacion.id), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin) {
            $form.delete(route('centros-formacion.destroy', centroFormacion.id))
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin}
                        <a use:inertia href={route('centros-formacion.index')} class="text-indigo-400 hover:text-indigo-600"> Centros de formación </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {centroFormacion.nombre}
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin ? undefined : true}>
                <div class="mt-4">
                    <Input label="Nombre" id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required />
                </div>

                <div class="mt-4">
                    <Input label="Código" id="codigo" type="number" input$min="0" input$max="2147483647" class="mt-1" bind:value={$form.codigo} error={errors.codigo} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="regional" value="Regional" />
                    <DynamicList id="regional_id" bind:value={$form.regional_id} routeWebApi={route('web-api.regionales')} placeholder="Busque por el nombre de la regional" message={errors.regional_id} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="subdirector_id" value="Subdirector" />
                    <DynamicList id="subdirector_id" bind:value={$form.subdirector_id} routeWebApi={route('web-api.users', 'subdirector')} placeholder="Busque por el nombre de subdirector" message={errors.subdirector_id} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="dinamizador_sennova_id" value="Dinamizador SENNOVA" />
                    <DynamicList id="dinamizador_sennova_id" bind:value={$form.dinamizador_sennova_id} routeWebApi={route('web-api.users', 'dinamizador')} placeholder="Busque por el nombre de subdirector" message={errors.dinamizador_sennova_id} required />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar centro de formación </button>
                {/if}
                {#if isSuperAdmin}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar centro de formación</LoadingButton>
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
