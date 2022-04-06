<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, useForm, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Input from '@/Shared/Input'
    import Label from '@/Shared/Label'
    import InputError from '@/Shared/InputError'
    import Button from '@/Shared/Button'
    import LoadingButton from '@/Shared/LoadingButton'
    import Select from '@/Shared/Select'
    import DynamicList from '@/Shared/Dropdowns/DynamicList'
    import Textarea from '@/Shared/Textarea'
    import Dialog from '@/Shared/Dialog'
    import InfoMessage from '@/Shared/InfoMessage'
    import Checkbox from '@smui/checkbox'
    import FormField from '@smui/form-field'

    export let errors
    export let convocatoria
    export let proyecto
    export let producto
    export let resultados
    export let actividadesRelacionadas
    export let tiposProducto

    $: $title = producto ? producto.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false
    let form = useForm({
        nombre: producto.nombre,
        resultado_id: producto.resultado_id,
        fecha_inicio: producto.fecha_inicio,
        fecha_finalizacion: producto.fecha_finalizacion,
        indicador: producto.indicador,
        tipo: {
            value: producto.producto_idi.tipo,
            label: tiposProducto.find((item) => item.value == producto.producto_idi.tipo)?.label,
        },
        subtipologia_minciencias_id: producto.producto_idi?.subtipologia_minciencias_id,
        actividad_id: actividadesRelacionadas,
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)) {
            $form.put(route('convocatorias.proyectos.productos.update', [convocatoria.id, proyecto.id, producto.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)) {
            $form.delete(route('convocatorias.proyectos.productos.destroy', [convocatoria.id, proyecto.id, producto.id]))
        }
    }

    let actividades = []
    let resultado_id = {
        value: producto.resultado_id,
        label: resultados.find((item) => item.value == producto.resultado_id)?.label,
        actividades: resultados.find((item) => item.value == producto.resultado_id)?.actividades,
    }

    $: if (resultado_id.value) {
        $form.resultado_id = resultado_id.value
        actividades = resultado_id.actividades
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin || checkPermission(authUser, [3, 4, 14])}
                        <a use:inertia href={route('convocatorias.proyectos.productos.index', [convocatoria.id, proyecto.id])} class="text-indigo-400 hover:text-indigo-600"> Productos </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {producto.nombre}
                </h1>
            </div>
        </div>
    </header>

    <div class="bg-white rounded shadow max-w-3xl">
        <form on:submit|preventDefault={submit}>
            <fieldset class="p-8" disabled={isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true) ? undefined : true}>
                <div class="mt-8 mb-8">
                    <Label class="text-center" required value="Fecha de ejecución" />
                    <div class="mt-4 flex items-start justify-around">
                        <div class="mt-4 flex">
                            <Label labelFor="fecha_inicio" value="Del" />
                            <div class="ml-4">
                                <input id="fecha_inicio" type="date" class="mt-1 block w-full p-4" min={proyecto.fecha_inicio} max={proyecto.fecha_finalizacion} bind:value={$form.fecha_inicio} required />
                            </div>
                        </div>
                        <div class="mt-4 flex">
                            <Label labelFor="fecha_finalizacion" value="hasta" />
                            <div class="ml-4">
                                <input id="fecha_finalizacion" type="date" class="mt-1 block w-full p-4" min={proyecto.fecha_inicio} max={proyecto.fecha_finalizacion} bind:value={$form.fecha_finalizacion} required />
                            </div>
                        </div>
                    </div>
                    {#if errors.fecha_inicio || errors.fecha_finalizacion}
                        <InputError message={errors.fecha_inicio || errors.fecha_finalizacion} />
                    {/if}
                </div>

                <hr />

                <div class="mt-8">
                    <InfoMessage>
                        <p>
                            Los productos pueden corresponder a bienes o servicios. Un bien es un objeto tangible, almacenable o transportable, mientras que el servicio es una prestación intangible.
                            <br />
                            El producto debe cumplir con la siguiente estructura:
                            <br />
                            Cuando el producto es un bien: nombre del bien + la condición deseada. Ejemplo: Vía construida.
                            <br />
                            Cuando el producto es un servicio: nombre del servicio + el complemento. Ejemplo: Servicio de asistencia técnica para el mejoramiento de hábitos alimentarios
                        </p>
                    </InfoMessage>
                    <Textarea disabled={isSuperAdmin ? false : true} label="Descripción" maxlength="40000" id="nombre" error={errors.nombre} bind:value={$form.nombre} required />
                </div>

                <div class="mt-8">
                    <Label required class="mb-4" labelFor="resultado_id" value="Resultado" />
                    <Select id="resultado_id" items={resultados} bind:selectedValue={resultado_id} error={errors.resultado_id} autocomplete="off" placeholder="Seleccione un resultado" required />
                </div>
                <div class="mt-8">
                    <Label required labelFor="indicador" value="Indicador" />
                    <InfoMessage class="mb-2" message="Especifique los medios de verificación para validar los logros del proyecto." />
                    <Textarea disabled={isSuperAdmin ? false : true} maxlength="40000" id="indicador" error={errors.indicador} bind:value={$form.indicador} required />
                </div>

                <div class="mt-8">
                    <Label required class="mb-4" labelFor="subtipologia_minciencias_id" value="Subtipología Minciencias" />
                    <DynamicList id="subtipologia_minciencias_id" bind:value={$form.subtipologia_minciencias_id} routeWebApi={route('web-api.subtipologias-minciencias')} placeholder="Busque por el nombre de la subtipología Minciencias" message={errors.subtipologia_minciencias_id} required />
                </div>

                <div class="mt-8">
                    <Select id="tipo-producto" items={tiposProducto} bind:selectedValue={$form.tipo} error={errors.tipo} autocomplete="off" placeholder="Seleccione un tipo" required />
                </div>

                <h6 class="mt-20 mb-12 text-2xl">Actividades</h6>
                <div class="bg-white rounded shadow overflow-hidden">
                    <div class="p-4">
                        <Label required class="mb-4" labelFor="actividad_id" value="Relacione alguna actividad" />
                        <InputError message={errors.actividad_id} />
                    </div>
                    <div class="grid grid-cols-2">
                        {#if actividades}
                            {#each actividades as { id, descripcion }, i}
                                <Label class="p-3 border-t border-b flex items-center text-sm" labelFor={'linea-tecnologica-' + id} value={descripcion} />

                                <div class="border-b border-t flex items-center justify-center">
                                    <input type="checkbox" bind:group={$form.actividad_id} id={'linea-tecnologica-' + id} value={id} class="rounded text-indigo-500" />
                                </div>
                            {/each}
                        {:else}
                            <p class="p-4">Sin información registrada</p>
                        {/if}
                    </div>
                </div>
            </fieldset>
            <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                {#if isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)}
                    <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar producto </button>
                {/if}
                {#if isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)}
                    <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar producto</LoadingButton>
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
