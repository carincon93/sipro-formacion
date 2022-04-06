<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import LoadingButton from '@/Shared/LoadingButton'
    import Label from '@/Shared/Label'
    import Switch from '@/Shared/Switch'
    import InputError from '@/Shared/InputError'
    import InfoMessage from '@/Shared/InfoMessage'

    export let errors
    export let proyecto

    $: $title = proyecto ? proyecto.codigo : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        modificable: proyecto.modificable,
    })

    function submit() {
        if (isSuperAdmin) {
            $form.put(route('proyectos.update', proyecto.id), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap flex items-center">
                    {#if isSuperAdmin}
                        <a use:inertia href={route('proyectos.index')} class="text-indigo-400 hover:text-indigo-600"> Proyectos </a>
                    {/if}
                    <span class="text-indigo-400 font-medium mx-1.5">/</span>
                    {proyecto.codigo}
                    <a class="bg-indigo-600 text-white p-1 pr-5 rounded ml-2" href={route('convocatorias.proyectos.edit', [proyecto.convocatoria_id, proyecto.id])} target="_blank">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <small> Ver detalles </small>
                        </span>
                    </a>
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8">
                <div class="mt-4">
                    <Label labelFor="modificable" value="¿El proyecto puede ser modificado?" class="inline-block mb-4" />
                    <br />
                    <Switch bind:checked={$form.modificable} />
                    <InputError message={errors.modificable} />

                    <InfoMessage class="mt-10" />
                </div>
            </fieldset>

            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Guardar</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
