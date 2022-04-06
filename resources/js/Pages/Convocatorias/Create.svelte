<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import InputError from '@/Shared/InputError'
    import LoadingButton from '@/Shared/LoadingButton'
    import Textarea from '@/Shared/Textarea'
    import Switch from '@/Shared/Switch'
    import Select from '@/Shared/Select'

    export let errors
    export let fases

    $: $title = 'Crear convocatoria'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        descripcion: '',
        esta_activa: false,
        fase: null,
        fecha_finalizacion_fase: '',
        min_fecha_inicio_proyectos_idi: '',
        max_fecha_finalizacion_proyectos_idi: '',
    })

    function submit() {
        if (isSuperAdmin) {
            $form.post(route('convocatorias.store'), {
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
                        <a use:inertia href={route('convocatorias.index')} class="text-indigo-400 hover:text-indigo-600"> Convocatorias </a>
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
                <div class="mt-4 mb-20 grid grid-cols-2">
                    <div>
                        <Label required class="mb-4" labelFor="fase" value="Fase" />
                    </div>
                    <div>
                        <Select id="fase" items={fases} bind:selectedValue={$form.fase} error={errors.fase} autocomplete="off" placeholder="Seleccione una fase" required />
                    </div>
                </div>

                <div class="mt-4 mb-20">
                    {#if $form.fase?.label}
                        <p class="text-center">Fecha de finalización de la fase: {$form.fase?.label.toLowerCase()}</p>
                    {/if}
                    <div class="mt-4 ">
                        <Label required labelFor="fecha_finalizacion_fase" value="Fecha límite" />
                        <Input id="fecha_finalizacion_fase" type="date" class="mt-1" bind:value={$form.fecha_finalizacion_fase} required />
                    </div>
                </div>
                {#if errors.fecha_finalizacion_fase}
                    <InputError message={errors.fecha_finalizacion_fase} />
                {/if}

                <div class="mt-4">
                    <Textarea label="Descripción" maxlength="40000" id="descripcion" error={errors.descripcion} bind:value={$form.descripcion} required />
                </div>

                <div class="mt-4 mb-20">
                    <Label required labelFor="esta_activa" value="¿Desea activar está convocatoria?" class="inline-block mb-4" />
                    <br />
                    <Switch bind:checked={$form.esta_activa} />
                    <InputError message={errors.esta_activa} />
                </div>

                <hr />

                <div class="mt-20">
                    <p class="text-center">Fechas máximas de ejecución de proyectos formativos</p>
                    <div class="mt-4 flex items-start justify-around">
                        <div class="mt-4 flex {errors.min_fecha_inicio_proyectos_idi ? '' : 'items-center'}">
                            <Label required labelFor="min_fecha_inicio_proyectos_idi" class={errors.min_fecha_inicio_proyectos_idi ? 'top-3.5 relative' : ''} value="Del" />
                            <div class="ml-4">
                                <Input id="min_fecha_inicio_proyectos_idi" type="date" class="mt-1" bind:value={$form.min_fecha_inicio_proyectos_idi} required />
                            </div>
                        </div>
                        <div class="mt-4 flex {errors.max_fecha_finalizacion_proyectos_idi ? '' : 'items-center'}">
                            <Label required labelFor="max_fecha_finalizacion_proyectos" class={errors.max_fecha_finalizacion_proyectos_idi ? 'top-3.5 relative' : ''} value="hasta" />
                            <div class="ml-4">
                                <Input id="max_fecha_finalizacion_proyectos" type="date" class="mt-1" bind:value={$form.max_fecha_finalizacion_proyectos_idi} required />
                            </div>
                        </div>
                    </div>
                </div>
                {#if errors.min_fecha_inicio_proyectos_idi || errors.max_fecha_finalizacion_proyectos_idi}
                    <InputError message={errors.min_fecha_inicio_proyectos_idi || errors.max_fecha_finalizacion_proyectos_idi} />
                {/if}
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Crear convocatoria</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
