<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import axios from 'axios'

    import Dialog from '@/Shared/Dialog'
    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import Textarea from '@/Shared/Textarea'
    import File from '@/Shared/File'
    import Button from '@/Shared/Button'
    import LoadingButton from '@/Shared/LoadingButton'
    import SelectMulti from '@/Shared/SelectMulti'
    import Select from '@/Shared/Select'

    export let errors
    export let semilleroInvestigacion
    export let lineasInvestigacion
    export let grupoInvestigacion
    export let redesConocimiento
    export let redesConocimientoSemilleroInvestigacion
    export let programasFormacionSemilleroInvestigacion
    export let lineasInvestigacionSemilleroInvestigacion

    $: $title = semilleroInvestigacion ? semilleroInvestigacion.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        _method: 'put',
        nombre: semilleroInvestigacion.nombre,
        fecha_creacion_semillero: semilleroInvestigacion.fecha_creacion_semillero,
        nombre_lider_semillero: semilleroInvestigacion.nombre_lider_semillero,
        email_contacto: semilleroInvestigacion.email_contacto,
        reconocimientos_semillero_investigacion: semilleroInvestigacion.reconocimientos_semillero_investigacion,
        vision: semilleroInvestigacion.vision,
        mision: semilleroInvestigacion.mision,
        objetivo_general: semilleroInvestigacion.objetivo_general,
        objetivos_especificos: semilleroInvestigacion.objetivos_especificos,
        link_semillero: semilleroInvestigacion.link_semillero,
        formato_gic_f_021: null,
        formato_gic_f_032: null,
        formato_aval_semillero: null,
        linea_investigacion_id: {
            value: lineasInvestigacion.find((item) => item.value == semilleroInvestigacion.linea_investigacion_id)?.value,
            label: lineasInvestigacion.find((item) => item.value == semilleroInvestigacion.linea_investigacion_id)?.label,
        },
        linea_investigacion: null,
        centro_formacion_id: semilleroInvestigacion.linea_investigacion?.grupo_investigacion?.centro_formacion_id,
        redes_conocimiento: redesConocimientoSemilleroInvestigacion.length > 0 ? redesConocimientoSemilleroInvestigacion : null,
        programas_formacion: programasFormacionSemilleroInvestigacion.length > 0 ? programasFormacionSemilleroInvestigacion : null,
        lineas_investigacion: lineasInvestigacionSemilleroInvestigacion.length > 0 ? lineasInvestigacionSemilleroInvestigacion : null,
    })

    function submit() {
        if (isSuperAdmin || checkRole(authUser, [4])) {
            $form.post(route('grupos-investigacion.semilleros-investigacion.update', [grupoInvestigacion.id, semilleroInvestigacion.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin) {
            $form.delete(route('grupos-investigacion.semilleros-investigacion.destroy', [grupoInvestigacion.id, semilleroInvestigacion.id]))
        }
    }

    let programasFormacion
    let oldLineaInvestigacionIdValue = null

    $: if ($form.linea_investigacion) {
        if (oldLineaInvestigacionIdValue != $form.linea_investigacion) {
            getProgramasFormacion($form.linea_investigacion?.value)
        }
    }
    async function getProgramasFormacion(lineaInvestigacionId) {
        let res = await axios.get(route('web-api.linea-investigacion-programa-formacion', lineaInvestigacionId))
        res.status == '200'
        programasFormacion = res.data
        oldLineaInvestigacionIdValue = $form.linea_investigacion
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1>
                    {#if isSuperAdmin || checkRole(authUser, [4])}
                        <a use:inertia href={route('grupos-investigacion.semilleros-investigacion.index', grupoInvestigacion.id)} class="text-indigo-400 hover:text-indigo-600"> Semilleros de investigación </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {semilleroInvestigacion.nombre}
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || checkRole(authUser, [4]) ? undefined : true}>
                <div class="mt-4">
                    <Label labelFor="nombre" value="Código" />
                    <Input disabled id="codigo" type="text" class="mt-1" bind:value={semilleroInvestigacion.codigo} error={errors.codigo} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="linea_investigacion_id" value="Línea de investigación principal" />
                    <Select id="linea_investigacion_id" items={lineasInvestigacion} bind:selectedValue={$form.linea_investigacion_id} error={errors.linea_investigacion_id} autocomplete="off" placeholder="Seleccione una línea de investigación" required />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="lineas_investigacion" value="Articulación con líneas de investigación" />
                    <SelectMulti id="lineas_investigacion" bind:selectedValue={$form.lineas_investigacion} items={lineasInvestigacion} isMulti={true} error={errors.lineas_investigacion} placeholder="Buscar por el nombre de la línea de investigación" required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="nombre" value="Nombre del semillero" />
                    <Input id="nombre" type="text" class="mt-1" bind:value={$form.nombre} error={errors.nombre} required />
                </div>

                <div class="mt-4 ">
                    <Label required labelFor="fecha_creacion_semillero" value="Fecha creación del semillero" />
                    <Input id="fecha_creacion_semillero" type="date" class="mt-1" bind:value={$form.fecha_creacion_semillero} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="nombre_lider_semillero" value="Nombre del líder del semillero" />
                    <Input id="nombre_lider_semillero" type="text" class="mt-1" bind:value={$form.nombre_lider_semillero} error={errors.nombre_lider_semillero} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="email_contacto" value="Email de contacto" />
                    <Input id="email_contacto" type="email" class="mt-1" bind:value={$form.email_contacto} error={errors.email_contacto} required />
                </div>

                <div class="mt-4">
                    <Label required labelFor="reconocimientos_semillero_investigacion" value="Reconocimientos semillero de investigación" />
                    <Textarea maxlength="40000" id="reconocimientos_semillero_investigacion" bind:value={$form.reconocimientos_semillero_investigacion} error={errors.reconocimientos_semillero_investigacion} required />
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
                    <Label labelFor="link_semillero" value="Link del semillero" />
                    <Input id="link_semillero" type="url" class="mt-1" bind:value={$form.link_semillero} error={errors.link_semillero} />
                </div>

                <div class="mt-4">
                    <Label required class="mb-4" labelFor="redes_conocimiento" value="Red o redes de conocimiento afines al Semillero de Investigación" />
                    <SelectMulti id="redes_conocimiento" bind:selectedValue={$form.redes_conocimiento} items={redesConocimiento} isMulti={true} error={errors.redes_conocimiento} placeholder="Buscar redes de conocimiento" required />
                </div>

                <hr class="mt-10 mb-10" />

                <div class="mt-4">
                    <Label required={$form.programas_formacion?.length > 0 ? undefined : 'required'} class="mb-4" labelFor="linea_investigacion" value="Seleccione una línea de investigación y posteriormente asocie los programas de formación" />
                    <Select id="linea_investigacion" items={lineasInvestigacion} bind:selectedValue={$form.linea_investigacion} error={errors.linea_investigacion} autocomplete="off" placeholder="Seleccione una línea de investigación" required={$form.programas_formacion?.length > 0 ? undefined : 'required'} />
                </div>

                {#if $form.linea_investigacion || programasFormacionSemilleroInvestigacion.length > 0}
                    <div class="mt-4">
                        <Label required class="mb-4" labelFor="programas_formacion" value="Programa(s) de formación" />
                        <SelectMulti id="programas_formacion" bind:selectedValue={$form.programas_formacion} items={programasFormacion} isMulti={true} error={errors.programas_formacion} placeholder="Programa(s) de formación" required />
                    </div>
                {/if}

                <hr class="mt-10 mb-10" />

                <div class="mt4-">
                    <Label class="mb-4 mt-8" labelFor="formato_gic_f_021" value="Formato GIC – F – 021" />
                    {#if semilleroInvestigacion.formato_gic_f_021}
                        <a target="_blank" class="text-indigo-400 underline inline-block mb-10 flex" download href={route('grupos-investigacion.semilleros-investigacion.download', [grupoInvestigacion.id, semilleroInvestigacion.id, 'formato_gic_f_021'])}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar formato GIC – F – 021
                        </a>
                    {:else if semilleroInvestigacion.formato_gic_f_021 == null && $form.formato_gic_f_021 == null}
                        <p class="my-10 text-red-400">No se ha cargado el formato GIC – F – 021</p>
                    {/if}
                    <File type="file" maxSize="10000" class="mt-1" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document" bind:value={$form.formato_gic_f_021} error={errors?.formato_gic_f_021} />
                </div>

                <hr class="mt-10 mb-10" />

                <div class="mt4-">
                    <Label class="mb-4 mt-8" labelFor="formato_gic_f_032" value="Formato GIC – F – 032" />
                    {#if semilleroInvestigacion.formato_gic_f_032}
                        <a target="_blank" class="text-indigo-400 underline inline-block mb-10 flex" download href={route('grupos-investigacion.semilleros-investigacion.download', [grupoInvestigacion.id, semilleroInvestigacion.id, 'formato_gic_f_032'])}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar formato GIC – F – 032
                        </a>
                    {:else if semilleroInvestigacion.formato_gic_f_032 == null && $form.formato_gic_f_032 == null}
                        <p class="my-10 text-red-400">No se ha cargado el formato GIC – F – 032</p>
                    {/if}
                    <File type="file" maxSize="10000" class="mt-1" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document" bind:value={$form.formato_gic_f_032} error={errors?.formato_gic_f_032} />
                </div>

                <hr class="mt-10 mb-10" />

                <div class="mt4-">
                    <Label class="mb-4 mt-8" labelFor="formato_aval_semillero" value="Aval del semillero" />
                    {#if semilleroInvestigacion.formato_aval_semillero}
                        <a target="_blank" class="text-indigo-400 underline inline-block mb-10 flex" download href={route('grupos-investigacion.semilleros-investigacion.download', [grupoInvestigacion.id, semilleroInvestigacion.id, 'formato_aval_semillero'])}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar aval del semillero
                        </a>
                    {:else if semilleroInvestigacion.formato_aval_semillero == null && $form.formato_aval_semillero == null}
                        <p class="my-10 text-red-400">No se ha cargado el aval del semillero</p>
                    {/if}
                    <File type="file" maxSize="10000" class="mt-1" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document" bind:value={$form.formato_aval_semillero} error={errors?.formato_aval_semillero} />
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin}
                    <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar semillero de investigación </button>
                {/if}
                {#if isSuperAdmin || checkRole(authUser, [4])}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar semillero de investigación</LoadingButton>
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
