<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import axios from 'axios'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import SelectMulti from '@/Shared/SelectMulti'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'

    export let errors
    export let grupoInvestigacion
    export let programasFormacion

    $: $title = 'Crear línea de investigación'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        nombre: '',
        grupo_investigacion_id: grupoInvestigacion.id,
        programas_formacion: null,
    })

    function submit() {
        if (isSuperAdmin || checkRole(authUser, [4])) {
            $form.post(route('grupos-investigacion.lineas-investigacion.store', grupoInvestigacion.id), {
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
                    {#if isSuperAdmin || checkRole(authUser, [4])}
                        <a use:inertia href={route('grupos-investigacion.lineas-investigacion.index', grupoInvestigacion.id)} class="text-indigo-400 hover:text-indigo-600"> Líneas de investigación </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    Crear
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || checkRole(authUser, [4]) ? undefined : true}>
                <div class="mt-4">
                    <Label required labelFor="nombre" value="Nombre de la línea de investigación" />
                    <Input id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="programas_formacion" value="Programa(s) de formación asociados" />
                    <SelectMulti id="programas_formacion" bind:selectedValue={$form.programas_formacion} items={programasFormacion} isMulti={true} error={errors.programas_formacion} placeholder="Buscar programas de formación" required />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin || checkRole(authUser, [4])}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Crear línea de investigación</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
