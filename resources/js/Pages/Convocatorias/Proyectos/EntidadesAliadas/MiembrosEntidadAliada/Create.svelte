<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import InfoMessage from '@/Shared/InfoMessage'
    import Checkbox from '@smui/checkbox'
    import FormField from '@smui/form-field'

    export let errors
    export let convocatoria
    export let proyecto
    export let entidadAliada
    export let tiposDocumento

    $: $title = 'Crear miembro de entidad aliada'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        nombre: '',
        email: '',
        tipo_documento: '',
        numero_documento: '',
        numero_celular: '',
        autorizacion_datos: false,
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true)) {
            $form.post(route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.store', [convocatoria.id, proyecto.id, entidadAliada.id]), {
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
                    {#if isSuperAdmin || checkPermission(authUser, [1])}
                        <a use:inertia href={route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.index', [convocatoria.id, proyecto.id, entidadAliada.id])} class="text-indigo-400 hover:text-indigo-600">Miembros de la entidad aliada</a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    Crear
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true) ? undefined : true}>
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
                    <InfoMessage message="Los datos proporcionados serán tratados de acuerdo con la política de tratamiento de datos personales del SENA y a la ley 1581 de 2012 (acuerdo No. 0009 del 2016)" />
                    <FormField>
                        <Checkbox bind:checked={$form.autorizacion_datos} />
                        <span slot="label">¿La persona autoriza el tratamiento de datos personales?. <a href="https://www.sena.edu.co/es-co/transparencia/Documents/proteccion_datos_personales_sena_2016.pdf" target="_blank" class="text-indigo-500">Leer acuerdo No. 0009 del 2016</a></span>
                    </FormField>
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true)}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit" bind:disabled={$form.autorizacion_datos}>Crear miembro de la entidad aliada</LoadingButton>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
