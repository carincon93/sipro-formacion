<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import File from '@/Shared/File'
    import Input from '@/Shared/Input'
    import Textarea from '@/Shared/Textarea'
    import Label from '@/Shared/Label'
    import Button from '@/Shared/Button'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'
    import Dialog from '@/Shared/Dialog'
    import SelectMulti from '@/Shared/SelectMulti'

    export let errors
    export let grupoInvestigacion
    export let categoriasMinciencias
    export let redesConocimiento
    export let redesConocimientoGrupoInvestigacion

    $: $title = grupoInvestigacion ? grupoInvestigacion.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        _method: 'put',
        nombre: grupoInvestigacion.nombre,
        acronimo: grupoInvestigacion.acronimo,
        email: grupoInvestigacion.email,
        enlace_gruplac: grupoInvestigacion.enlace_gruplac,
        codigo_minciencias: grupoInvestigacion.codigo_minciencias,
        mision: grupoInvestigacion.mision,
        categoria_minciencias: {
            value: grupoInvestigacion.categoria_minciencias,
            label: categoriasMinciencias.find((item) => item.value == grupoInvestigacion.categoria_minciencias)?.label,
        },
        fecha_creacion_grupo: grupoInvestigacion.fecha_creacion_grupo,
        nombre_lider_grupo: grupoInvestigacion.nombre_lider_grupo,
        email_contacto: grupoInvestigacion.email_contacto,
        reconocimientos_grupo_investigacion: grupoInvestigacion.reconocimientos_grupo_investigacion,
        programa_nal_ctei_principal: grupoInvestigacion.programa_nal_ctei_principal,
        programa_nal_ctei_secundaria: grupoInvestigacion.programa_nal_ctei_secundaria,
        vision: grupoInvestigacion.vision,
        mision: grupoInvestigacion.mision,
        objetivo_general: grupoInvestigacion.objetivo_general,
        objetivos_especificos: grupoInvestigacion.objetivos_especificos,
        link_propio_grupo: grupoInvestigacion.link_propio_grupo,
        formato_gic_f_020: null,
        formato_gic_f_032: null,
        redes_conocimiento: redesConocimientoGrupoInvestigacion.length > 0 ? redesConocimientoGrupoInvestigacion : null,
        centro_formacion_id: grupoInvestigacion.centro_formacion_id,
    })

    function submit() {
        if (isSuperAdmin) {
            $form.post(route('grupos-investigacion.update', grupoInvestigacion.id), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin) {
            $form.delete(route('grupos-investigacion.destroy', grupoInvestigacion.id))
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin}
                        <a use:inertia href={route('grupos-investigacion.index')} class="text-indigo-400 hover:text-indigo-600"> Grupos de investigación </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {grupoInvestigacion.nombre}
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
                    <Label required labelFor="email" value="Acrónimo" />
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
                    <Select id="categoria_minciencias" items={categoriasMinciencias} bind:selectedValue={$form.categoria_minciencias} error={errors.categoria_minciencias} autocomplete="off" placeholder="Seleccione una categoría Minciencias" required />
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
                    <Label class="mb-4 mt-8" labelFor="formato_gic_f_020" value="Formato GIC – F – 020" />
                    {#if grupoInvestigacion.formato_gic_f_020}
                        <a target="_blank" class="text-indigo-400 underline inline-block mb-10 flex" download href={route('grupos-investigacion.download', [grupoInvestigacion.id, 'formato_gic_f_020'])}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar formato GIC – F – 020
                        </a>
                    {:else}
                        <p class="my-10 text-red-400">No se ha cargado el formato GIC – F – 020</p>
                    {/if}
                    <File type="file" maxSize="10000" class="mt-1" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document" bind:value={$form.formato_gic_f_020} error={errors?.formato_gic_f_020} />
                </div>

                <hr class="mt-10 mb-10" />

                <div class="mt4-">
                    <Label class="mb-4 mt-8" labelFor="formato_gic_f_032" value="Formato GIC – F – 032" />
                    {#if grupoInvestigacion.formato_gic_f_032}
                        <a target="_blank" class="text-indigo-400 underline inline-block mb-10 flex" download href={route('grupos-investigacion.download', [grupoInvestigacion.id, 'formato_gic_f_032'])}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar formato GIC – F – 032
                        </a>
                    {:else}
                        <p class="my-10 text-red-400">No se ha cargado el formato GIC – F – 032</p>
                    {/if}
                    <File type="file" maxSize="10000" class="mt-1" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document" bind:value={$form.formato_gic_f_032} error={errors?.formato_gic_f_032} />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar grupo de investigación </button>
                {/if}
                {#if isSuperAdmin}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar grupo de investigación</LoadingButton>
                {/if}
            </div>
        </form>
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
