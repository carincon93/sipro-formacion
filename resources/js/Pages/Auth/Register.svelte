<script context="module">
    import GuestLayout from '@/Layouts/Guest'
    export const layout = GuestLayout
</script>

<script>
    import { inertia, useForm } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import LoadingButton from '@/Shared/LoadingButton'
    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import InputError from '@/Shared/InputError'
    import Select from '@/Shared/Select'
    import InfoMessage from '@/Shared/InfoMessage'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'
    import Checkbox from '@smui/checkbox'
    import FormField from '@smui/form-field'

    export let errors
    export let tiposDocumento
    export let tiposVinculacion
    export let roles

    let sending = false

    let form = useForm({
        nombre: '',
        email: '',
        password: '',
        password_confirmation: '',
        tipo_documento: '',
        numero_documento: '',
        numero_celular: '',
        tipo_vinculacion: '',
        centro_formacion_id: null,
        autorizacion_datos: false,
        role_id: [],
    })

    function handleSubmit() {
        if ($form.autorizacion_datos) {
            $form.post(route('register'))
        }
    }
</script>

<form on:submit|preventDefault={handleSubmit}>
    <div>
        <Input label="Nombre completo" id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required autocomplete="nombre" />
    </div>

    <div class="mt-4">
        <Input label="Correo electrónico institucional" id="email" type="email" class="mt-1" bind:value={$form.email} error={errors.email} required autocomplete="username" />
    </div>

    <div class="mt-4">
        <Input label={$_('Password')} id="password" type="password" class="mt-1" bind:value={$form.password} error={errors.password} required autocomplete="new-password" />
    </div>

    <div class="mt-4">
        <Input label={$_('Confirm Password')} id="password_confirmation" type="password" class="mt-1" bind:value={$form.password_confirmation} error={errors.password_confirmation} required autocomplete="new-password" />
    </div>

    <div class="mt-4">
        <Label required class="mb-4" labelFor="tipo_documento" value="Tipo de documento" />
        <Select id="tipo_documento" items={tiposDocumento} bind:selectedValue={$form.tipo_documento} error={errors.tipo_documento} autocomplete="off" placeholder="Seleccione un tipo de documento" required />
    </div>

    <div class="mt-4">
        <Input label="Número de documento" id="numero_documento" type="number" input$min="55555" input$max="3900000000" class="mt-1" bind:value={$form.numero_documento} error={errors.numero_documento} required />
    </div>

    <div class="mt-4">
        <Input label="Número de celular" id="numero_celular" type="number" input$min="3000000000" input$max="3900000000" class="mt-1" bind:value={$form.numero_celular} error={errors.numero_celular} required />
    </div>

    <div class="mt-4">
        <Label required class="mb-4" labelFor="tipo_vinculacion" value="Tipo de vinculación" />
        <Select id="tipo_vinculacion" items={tiposVinculacion} bind:selectedValue={$form.tipo_vinculacion} error={errors.tipo_vinculacion} autocomplete="off" placeholder="Seleccione el tipo de vinculación" required />
    </div>

    <div class="mt-4">
        <Label required class="mb-4" labelFor="centro_formacion_id" value="Centro de formación" />
        <DynamicList id="centro_formacion_id" bind:value={$form.centro_formacion_id} routeWebApi={route('web-api.centros-formacion')} placeholder="Busque por el nombre del centro de formación" message={errors.centro_formacion_id} required />
    </div>

    <div class="mt-4">
        <Label required class="mb-4" labelFor="role_id" value="Seleccione algún rol" />
        <InputError message={errors.role_id} />
    </div>
    <div class="grid grid-cols-2">
        {#each roles as { id, name }, i}
            <FormField class="mt-2 border-r border-t">
                <Checkbox bind:group={$form.role_id} value={id} />
                <span slot="label" class="first-letter-uppercase inline-block">{name}</span>
            </FormField>
        {/each}
    </div>

    <hr class="mt-4" />

    <div class="block mt-4">
        <InfoMessage message="Los datos proporcionados serán tratados de acuerdo con la política de tratamiento de datos personales del SENA y a la ley 1581 de 2012 (Acuerdo No. 0009 del 2016)" class="mb-4" />
        <FormField>
            <Checkbox bind:checked={$form.autorizacion_datos} />
            <span slot="label">Autorizo el tratamiento de mis datos personales. <a href="https://www.sena.edu.co/es-co/transparencia/Documents/proteccion_datos_personales_sena_2016.pdf" target="_blank" class="text-indigo-500">Leer acuerdo No. 0009 del 2016</a></span>
        </FormField>
    </div>

    <div class="flex items-center justify-end mt-4">
        <a use:inertia href={route('login')} class="mr-4 underline text-sm text-gray-600 hover:text-gray-900">
            {$_('Already registered?')}
        </a>

        <LoadingButton bind:loading={sending} class="btn-indigo" type="submit" bind:disabled={$form.autorizacion_datos}>{$_('Register')}</LoadingButton>
    </div>
</form>
