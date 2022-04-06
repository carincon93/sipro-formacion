<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'

    export let errors
    export let sectoresProductivos
    export let mesasTecnicas

    $: $title = 'Crear tema priorizado'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        nombre: '',
        mesa_tecnica_id: null,
        sector_productivo_id: null,
    })

    function submit() {
        if (isSuperAdmin) {
            $form.post(route('temas-priorizados.store'), {
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
                        <a use:inertia href={route('temas-priorizados.index')} class="text-indigo-400 hover:text-indigo-600"> Temas priorizados </a>
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
                    <Label required class="mb-4" labelFor="sector_productivo_id" value="Sector productivo" />
                    <Select id="sector_productivo_id" items={sectoresProductivos} bind:selectedValue={$form.sector_productivo_id} error={errors.sector_productivo_id} autocomplete="off" placeholder="Seleccione un sector productivo" required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="mesa_tecnica_id" value="Mesa técnica de servicios tecnológicos" />
                    <Select id="mesa_tecnica_id" items={mesasTecnicas} bind:selectedValue={$form.mesa_tecnica_id} error={errors.mesa_tecnica_id} autocomplete="off" placeholder="Seleccione una mesta técnica de servicios tecnológicos" required />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Crear tema priorizado</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
