<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'

    export let errors

    $: $title = 'Crear regionales'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        nombre: '',
        codigo: '',
        region_id: null,
        director_regional_id: null,
    })

    function submit() {
        if (isSuperAdmin) {
            $form.post(route('regionales.store'), {
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
                    {#if isSuperAdmin}
                        <a use:inertia href={route('regionales.index')} class="text-indigo-400 hover:text-indigo-600"> Regionales </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    Crear
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
                    <Label required class="mb-4" labelFor="region_id" value="Región" />
                    <DynamicList id="region_id" bind:value={$form.region_id} routeWebApi={route('web-api.regiones')} placeholder="Busque por el nombre de la región" message={errors.region_id} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="director_regional_id" value="Director(a) Regional" />
                    <DynamicList id="director_regional_id" bind:value={$form.director_regional_id} routeWebApi={route('web-api.users', 'director regional')} placeholder="Busque por el nombre del director" message={errors.director_regional_id} required />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Crear regional</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
