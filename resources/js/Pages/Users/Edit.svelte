<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import InputError from '@/Shared/InputError'
    import LoadingButton from '@/Shared/LoadingButton'
    import Switch from '@/Shared/Switch'
    import Button from '@/Shared/Button'
    import Select from '@/Shared/Select'
    import Checkbox from '@smui/checkbox'
    import FormField from '@smui/form-field'
    import Dialog from '@/Shared/Dialog'
    import InfoMessage from '@/Shared/InfoMessage'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'
    import DataTableMenu from '@/Shared/DataTableMenu'
    import { Item, Text } from '@smui/list'
    import { Inertia } from '@inertiajs/inertia'

    export let errors
    export let usuario
    export let tiposDocumento
    export let tiposVinculacion
    export let roles
    export let rolesRelacionados
    export let permisosRelacionados
    export let proyectos

    $: $title = usuario ? usuario.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        _token: $page.props.csrf_token,
        nombre: usuario.nombre,
        email: usuario.email,
        password: usuario.password,
        tipo_documento: {
            value: usuario.tipo_documento,
            label: tiposDocumento.find((item) => item.value == usuario.tipo_documento)?.label,
        },
        numero_documento: usuario.numero_documento,
        numero_celular: usuario.numero_celular,
        habilitado: usuario.habilitado,
        tipo_vinculacion: {
            value: usuario.tipo_vinculacion,
            label: tiposVinculacion.find((item) => item.value == usuario.tipo_vinculacion)?.label,
        },
        centro_formacion_id: usuario.centro_formacion_id,
        role_id: rolesRelacionados,
        permission_id: permisosRelacionados,
        autorizacion_datos: usuario.autorizacion_datos,
        default_password: false,
    })

    function submit() {
        if (isSuperAdmin || checkRole(authUser, [4, 21])) {
            $form.put(route('users.update', usuario.id), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin || checkRole(authUser, [4])) {
            $form.delete(route('users.destroy', usuario.id))
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin || checkRole(authUser, [4, 21])}
                        <a use:inertia href={route('users.index')} class="text-indigo-400 hover:text-indigo-600"> Usuarios </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {usuario.nombre}
                </h1>
            </div>
        </div>
    </header>

    <form on:submit|preventDefault={submit}>
        <div class="bg-white rounded shadow max-w-3xl">
            <fieldset class="p-8" disabled={isSuperAdmin || checkRole(authUser, [4, 21]) ? undefined : true}>
                <div class="mt-4">
                    <Input label="Nombre completo" id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required />
                </div>

                <div class="mt-4">
                    <Input label="Correo electrónico institucional" id="email" type="email" class="mt-1" bind:value={$form.email} error={errors.email} required />
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
                    <Label required labelFor="habilitado" value="¿Usuario habilitado para ingresar al sistema?" class="inline-block mb-4" />
                    <br />
                    <Switch bind:checked={$form.habilitado} />
                    <InputError message={errors.habilitado} />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="tipo_vinculacion" value="Tipo de vinculación" />
                    <Select id="tipo_vinculacion" items={tiposVinculacion} bind:selectedValue={$form.tipo_vinculacion} error={errors.tipo_vinculacion} autocomplete="off" placeholder="Seleccione el tipo de vinculación" required />
                </div>

                {#if isSuperAdmin}
                    <div class="mt-4">
                        <Label required class="mb-4" labelFor="centro_formacion_id" value="Centro de formación" />
                        <DynamicList id="centro_formacion_id" bind:value={$form.centro_formacion_id} routeWebApi={route('web-api.centros-formacion')} placeholder="Busque por el nombre del centro de formación" message={errors.centro_formacion_id} required />
                    </div>
                {/if}

                <div class="mt-4">
                    <Label required labelFor="default_password" value="¿Usar contraseña por defecto?" class="inline-block mb-4" />
                    <br />
                    <Switch bind:checked={$form.default_password} />
                </div>

                <div class="mt-4">
                    <InfoMessage message={usuario.autorizacion_datos ? 'Está persona autorizó el tratamiento de datos' : 'Está persona no autorizó el tratamiento de datos'} />
                </div>

                <div class="mt-4">
                    {#if $form.numero_documento && $form.default_password}
                        <InfoMessage message="La contraseña de este usuario es: sena{$form.numero_documento}*" />
                    {/if}
                </div>
            </fieldset>
        </div>

        <div class="bg-white rounded shadow overflow-hidden mt-20">
            <fieldset class="p-8" disabled={isSuperAdmin || checkRole(authUser, [4, 21]) ? undefined : true}>
                <div class="p-4">
                    <Label required class="mb-4" labelFor="role_id" value="Seleccione algún rol" />
                    <InputError message={errors.role_id} />
                </div>
                <div class="grid grid-cols-2">
                    {#each roles as { id, name }, i}
                        {#if checkRole(authUser, [4, 21]) && name == 'aprendiz'}
                            <div class="pt-8 pb-8 border-t">
                                <FormField>
                                    <Checkbox bind:group={$form.role_id} value={id} />
                                    <span slot="label">{name}</span>
                                </FormField>
                            </div>
                        {:else if isSuperAdmin}
                            <div class="pt-8 pb-8 border-t">
                                <FormField>
                                    <Checkbox bind:group={$form.role_id} value={id} />
                                    <span slot="label">{name}</span>
                                </FormField>
                            </div>
                        {/if}
                    {/each}
                </div>
            </fieldset>
        </div>

        <div class="bg-white rounded shadow overflow-hidden mt-20">
            <fieldset class="p-8" disabled={isSuperAdmin}>
                <div class="p-4">
                    <Label class="mb-4" labelFor="role_id" value="Seleccione algún permiso especial para el usuario (Aplica cuando la convocatoria ha finalizado)" />
                    <InputError message={errors.permission_id} />
                </div>
                <div class="grid grid-cols-2">
                    {#if isSuperAdmin}
                        <div class="pt-8 pb-8 border-t">
                            <FormField>
                                <Checkbox bind:group={$form.permission_id} value={1} />
                                <span slot="label">Crear proyecto formativo</span>
                            </FormField>
                        </div>
                    {/if}
                </div>
            </fieldset>
        </div>

        <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
            {#if isSuperAdmin || checkRole(authUser, [4])}
                <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar usuario </button>
            {/if}
            {#if isSuperAdmin || checkRole(authUser, [4, 21])}
                <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar usuario</LoadingButton>
            {/if}
        </div>
    </form>

    <h1 class="mt-24 mb-8 text-center text-3xl">Proyectos asociados</h1>
    <div class="bg-white rounded shadow">
        <table class="w-full whitespace-no-wrap table-fixed data-table">
            <thead>
                <tr class="text-left font-bold">
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Código </th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Título </th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Fecha de ejecución </th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center th-actions"> Acciones </th>
                </tr>
            </thead>

            <tbody>
                {#each proyectos as proyecto}
                    <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                        <td class="border-t">
                            <p class="px-6 py-4 focus:text-indigo-500">
                                {proyecto.codigo}
                            </p>
                        </td>
                        <td class="border-t">
                            <p class="px-6 py-4 focus:text-indigo-500">
                                {proyecto.idi ? proyecto.idi.titulo : proyecto.cultura_innovacion ? proyecto.cultura_innovacion.titulo : proyecto.servicio_tecnologico ? proyecto.servicio_tecnologico.titulo : proyecto.tp?.nodo_tecnoparque ? proyecto.tp?.titulo : proyecto.tecnoacademia_lineas_tecnoacademia ? proyecto.tecnoacademia_lineas_tecnoacademia[0]?.tecnoacademia.nombre : null}
                            </p>
                        </td>
                        <td class="border-t">
                            <p class="px-6 py-4">
                                {proyecto.idi ? proyecto.idi.fecha_ejecucion : proyecto.cultura_innovacion ? proyecto.cultura_innovacion.fecha_ejecucion : proyecto.servicio_tecnologico ? proyecto.servicio_tecnologico.fecha_ejecucion : proyecto.ta ? proyecto.ta.fecha_ejecucion : proyecto.tp ? proyecto.tp?.fecha_ejecucion : null}
                            </p>
                        </td>
                        <td class="border-t td-actions">
                            <DataTableMenu class={proyecto.length < 4 ? 'z-50' : ''}>
                                {#if isSuperAdmin || checkPermission(authUser, [3, 4])}
                                    <Item on:SMUI:action={() => Inertia.visit(route('convocatorias.proyectos.edit', [proyecto.convocatoria_id, proyecto.id]))}>
                                        <Text>Ver detalles</Text>
                                    </Item>
                                {:else}
                                    <Item>
                                        <Text>No tiene permisos</Text>
                                    </Item>
                                {/if}
                            </DataTableMenu>
                        </td>
                    </tr>
                {/each}

                {#if proyectos.length === 0}
                    <tr>
                        <td class="border-t px-6 py-4" colspan="4"> Sin información registrada </td>
                    </tr>
                {/if}
            </tbody>
        </table>
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
