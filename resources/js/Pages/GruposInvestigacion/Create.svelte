<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Textarea from '@/Shared/Textarea'
    import File from '@/Shared/File'
    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import SelectMulti from '@/Shared/SelectMulti'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'

    export let errors
    export let categoriasMinciencias
    export let redesConocimiento

    $: $title = 'Crear grupo de investigación'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let sending = false
    let form = useForm({
        nombre: '',
        acronimo: '',
        email: '',
        enlace_gruplac: '',
        codigo_minciencias: '',
        categoria_minciencias: '',
        fecha_creacion_grupo: '',
        nombre_lider_grupo: '',
        email_contacto: '',
        reconocimientos_grupo_investigacion: '',
        programa_nal_ctei_principal: '',
        programa_nal_ctei_secundaria: '',
        vision: '',
        mision: '',
        objetivo_general: '',
        objetivos_especificos: '',
        link_propio_grupo: '',
        formato_gic_f_020: '',
        formato_gic_f_032: '',
        centro_formacion_id: null,
        redes_conocimiento: null,
    })

    function submit() {
        if (isSuperAdmin) {
            $form.post(route('grupos-investigacion.store'), {
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
                        <a use:inertia href={route('grupos-investigacion.index')} class="text-indigo-400 hover:text-indigo-600"> Grupos de investigación </a>
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
                    <Label required class="mb-4" labelFor="centro_formacion_id" value="Centro de formación" />
                    <DynamicList id="centro_formacion_id" bind:value={$form.centro_formacion_id} routeWebApi={route('web-api.centros-formacion')} placeholder="Busque por el nombre del centro de formación" message={errors.centro_formacion_id} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="nombre" value="Nombre del grupo de investigación" />
                    <Input id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="acronimo" value="Acrónimo" />
                    <Input id="acronimo" type="text" class="mt-1" bind:value={$form.acronimo} error={errors.acronimo} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="email" value="Correo electrónico" />
                    <Input id="email" type="email" class="mt-1" bind:value={$form.email} error={errors.email} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="enlace_gruplac" value="Enlace GrupLAC" />
                    <Input id="enlace_gruplac" type="url" class="mt-1" bind:value={$form.enlace_gruplac} error={errors.enlace_gruplac} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="codigo_minciencias" value="Código Minciencias" />
                    <Input id="codigo_minciencias" type="text" class="mt-1" bind:value={$form.codigo_minciencias} error={errors.codigo_minciencias} required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="categoria_minciencias" value="Clasificación MinCiencias 894 – 2021" />
                    <Select id="categoria_minciencias" items={categoriasMinciencias} bind:selectedValue={$form.categoria_minciencias} error={errors.categoria_minciencias} autocomplete="off" placeholder="Seleccione la clasificación MinCiencias 894 – 2021" required />
                </div>

                <div class="mt-4 ">
                    <Label required labelFor="fecha_creacion_grupo" value="Fecha creación del grupo" />
                    <Input id="fecha_creacion_grupo" type="date" class="mt-1" bind:value={$form.fecha_creacion_grupo} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="nombre_lider_grupo" value="Nombre del líder del grupo" />
                    <Input id="nombre_lider_grupo" type="text" class="mt-1" bind:value={$form.nombre_lider_grupo} error={errors.nombre_lider_grupo} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="email_contacto" value="Email de contacto" />
                    <Input id="email_contacto" type="email" class="mt-1" bind:value={$form.email_contacto} error={errors.email_contacto} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="reconocimientos_grupo_investigacion" value="Reconocimientos grupo de investigación" />
                    <Textarea maxlength="40000" id="reconocimientos_grupo_investigacion" bind:value={$form.reconocimientos_grupo_investigacion} error={errors.reconocimientos_grupo_investigacion} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="vision" value="Visión" />
                    <Textarea maxlength="40000" id="vision" bind:value={$form.vision} error={errors.vision} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="mision" value="Misión" />
                    <Textarea maxlength="40000" id="mision" bind:value={$form.mision} error={errors.mision} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="objetivo_general" value="Objetivo general" />
                    <Textarea maxlength="40000" id="objetivo_general" bind:value={$form.objetivo_general} error={errors.objetivo_general} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="objetivos_especificos" value="Objetivos específicos " />
                    <Textarea maxlength="40000" id="objetivos_especificos" bind:value={$form.objetivos_especificos} error={errors.objetivos_especificos} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="programa_nal_ctei_principal" value="Programa Nal. CTeI (Principal)" />
                    <Input id="programa_nal_ctei_principal" type="text" class="mt-1" bind:value={$form.programa_nal_ctei_principal} error={errors.programa_nal_ctei_principal} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="programa_nal_ctei_secundaria" value="Programa Nal. CTeI (Secundaria)" />
                    <Input id="programa_nal_ctei_secundaria" type="text" class="mt-1" bind:value={$form.programa_nal_ctei_secundaria} error={errors.programa_nal_ctei_secundaria} required />
                </div>

                <div class="mt-4">
                    <Label labelFor="link_propio_grupo" value="Link propio del grupo" />
                    <Input id="link_propio_grupo" type="url" class="mt-1" bind:value={$form.link_propio_grupo} error={errors.link_propio_grupo} />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="redes_conocimiento" value="Red o redes de conocimiento afines al Grupo de Investigación" />
                    <SelectMulti id="redes_conocimiento" bind:selectedValue={$form.redes_conocimiento} items={redesConocimiento} isMulti={true} error={errors.redes_conocimiento} placeholder="Buscar redes de conocimiento" required />
                </div>

                <hr class="mt-10 mb-10" />

                <div class="mt4-">
                    <Label class="mb-4 mt-8" labelFor="formato_gic_f_020" value="GIC – F – 020" />
                    <File type="file" maxSize="10000" class="mt-1" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document" bind:value={$form.formato_gic_f_020} error={errors?.formato_gic_f_020} />
                </div>

                <hr class="mt-10 mb-10" />

                <div class="mt4-">
                    <Label class="mb-4 mt-8" labelFor="formato_gic_f_032" value="GIC – F – 032" />
                    <File type="file" maxSize="10000" class="mt-1" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document" bind:value={$form.formato_gic_f_032} error={errors?.formato_gic_f_032} />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 sticky bottom-0">
                {#if isSuperAdmin}
                    <div class="flex items-center">
                        <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Crear grupo de investigación</LoadingButton>
                    </div>
                    <p class="mt-4"><strong>Nota: </strong>Después de crear el grupo de investigación será redirigido(a) a una nueva sección para añadir las líneas de investigación declaradas y el alineamiento con los programas de formación</p>
                {/if}
            </div>
        </form>
    </div>
</AuthenticatedLayout>
