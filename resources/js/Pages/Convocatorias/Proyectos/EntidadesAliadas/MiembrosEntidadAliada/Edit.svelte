<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import Button from '@/Shared/Button'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import Dialog from '@/Shared/Dialog'
    import InfoMessage from '@/Shared/InfoMessage'

    export let errors
    export let convocatoria
    export let proyecto
    export let entidadAliada
    export let miembroEntidadAliada
    export let tiposDocumento

    $: $title = miembroEntidadAliada ? miembroEntidadAliada.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        nombre: miembroEntidadAliada.nombre,
        email: miembroEntidadAliada.email,
        tipo_documento: {
            value: miembroEntidadAliada.tipo_documento,
            label: tiposDocumento.find((item) => item.value == miembroEntidadAliada.tipo_documento)?.label,
        },
        numero_documento: miembroEntidadAliada.numero_documento,
        numero_celular: miembroEntidadAliada.numero_celular,
        autorizacion_datos: miembroEntidadAliada.autorizacion_datos,
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)) {
            $form.put(route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.update', [convocatoria.id, proyecto.id, entidadAliada.id, miembroEntidadAliada.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)) {
            $form.delete(route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.destroy', [convocatoria.id, proyecto.id, entidadAliada.id, miembroEntidadAliada.id]))
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin || checkPermission(authUser, [3, 4, 14])}
                        <a use:inertia href={route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.index', [convocatoria.id, proyecto.id, entidadAliada.id])} class="text-indigo-400 hover:text-indigo-600">Miembros de la entidad aliada</a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {miembroEntidadAliada.nombre}
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true) ? undefined : true}>
                <div class="mt-4">
                    <Input label="Nombre completo" id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required />
                </div>

                <div class="mt-4">
                    <Input label="Correo electrónico" id="email" type="email" class="mt-1" bind:value={$form.email} error={errors.email} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="tipo_documento" value="Tipo de documento" />
                    <Select id="tipo_documento" items={tiposDocumento} bind:selectedValue={$form.tipo_documento} error={errors.tipo_documento} autocomplete="off" placeholder="Seleccione un tipo de documento" required />
                </div>

                <div class="mt-4">
                    <Input label="Número de documento" id="numero_documento" type="number" input$min="55555" input$max="9999999999999" class="mt-1" bind:value={$form.numero_documento} error={errors.numero_documento} required />
                </div>

                <div class="mt-4">
                    <Input label="Número de celular" id="numero_celular" type="number" input$min="3000000000" input$max="9999999999" class="mt-1" bind:value={$form.numero_celular} error={errors.numero_celular} required />
                </div>

                <div class="mt-4">
                    <InfoMessage message={miembroEntidadAliada.autorizacion_datos ? 'Está persona autorizó el tratamiento de datos' : 'Está persona no autorizó el tratamiento de datos'} />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)}
                    <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar miembro de la entidad aliada </button>
                {/if}
                {#if isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar miembro de la entidad aliada</LoadingButton>
                {/if}
            </div>
        </form>

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
    </div>
</AuthenticatedLayout>
