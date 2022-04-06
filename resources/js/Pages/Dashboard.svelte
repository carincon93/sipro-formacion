<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import { Inertia } from '@inertiajs/inertia'

    import Button from '@/Shared/Button'
    import Dialog from '@/Shared/Dialog'

    $title = 'Panel de control'

    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = true
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div class="max-w-2xl">
                <h1 class="text-4xl">
                    ¡Bienvenido(a)
                    <span class="capitalize">{$page.props.auth.user.nombre}</span>!
                </h1>
                {#if checkRole(authUser, [1])}
                    <p class="text-indigo-700 mt-4">Su rol principal es: Administrador</p>
                {:else if checkRole(authUser, [2])}
                    <p class="text-indigo-700 mt-4">Su rol principal es: Director regional</p>
                {:else if checkRole(authUser, [3])}
                    <p class="text-indigo-700 mt-4">Su rol principal es: Subdirector de centro de formación</p>
                {:else if checkRole(authUser, [4])}
                    <p class="text-indigo-700 mt-4">Su rol principal es: Dinamizador (a) SENNOVA</p>
                {:else if checkRole(authUser, [21])}
                    <p class="text-indigo-700 mt-4">Su rol principal es: Líder de grupo de investigación</p>
                {:else}
                    <p class="text-2xl mt-4">Formule proyectos formativos.</p>
                {/if}

                {#if isSuperAdmin || checkPermission(authUser, [1, 3, 4, 14])}
                    <Button variant="raised" on:click={() => Inertia.visit(route('convocatorias.index'))} class="mt-4 inline-block">Revisar convocatorias</Button>
                {/if}
            </div>
            <div>
                <figure>
                    <img src={window.basePath + '/images/dashboard.png'} alt="" />
                </figure>
            </div>
        </div>
    </header>
    <div class="py-12">
        <div class="grid grid-cols-3 gap-10">
            {#if isSuperAdmin}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('centros-formacion.index')}>Centros de formación</a>
            {/if}

            {#if isSuperAdmin || checkPermission(authUser, [1, 3, 4, 14])}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('convocatorias.index')}>Convocatorias</a>
            {/if}

            {#if isSuperAdmin || checkRole(authUser, [4])}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('grupos-investigacion.index')}>Grupos, líneas y semilleros de investigación</a>
            {/if}

            {#if isSuperAdmin}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('mesas-tecnicas.index')}>Mesas técnicas</a>
            {/if}

            {#if isSuperAdmin || checkRole(authUser, [4])}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('programas-formacion.index')}>Programas de formación</a>
            {/if}

            {#if isSuperAdmin}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('proyectos.index')}>Proyectos</a>
            {/if}

            {#if isSuperAdmin}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('redes-conocimiento.index')}>Redes de conocimiento</a>
            {/if}

            {#if isSuperAdmin}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('regionales.index')}>Regionales</a>
            {/if}

            {#if isSuperAdmin}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('roles.index')}>Roles de sistema</a>
            {/if}

            {#if isSuperAdmin}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('tematicas-estrategicas.index')}>Temáticas estratégicas SENA</a>
            {/if}

            {#if isSuperAdmin || checkRole(authUser, [4, 21])}
                <a use:inertia class="bg-white overflow-hidden shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col" href={route('users.index')}>Usuarios</a>
            {/if}
        </div>
    </div>

    <!-- <Dialog bind:open={dialogOpen} id="informacion">
        <div slot="title" class="flex items-center flex-col mb-10">Importante</div>
        <div slot="content">
            <small>Noviembre 4</small>

            <hr class="mt-10 mb-10" />
            <div>
                <p>
                    Actualmente la plataforma SGPS-SIPRO permite consultar el estado del proyecto y las observaciones formuladas por el equipo evaluador al término de la Segunda Etapa de Evaluación, sin embargo, de acuerdo con los Lineamientos de la Convocatoria, el equipo SENNOVA verificará la aplicación final de causales de rechazo y las reglas relativas a la bolsa regional; lo que podrá dar
                    lugar a cambios en la información actual. Los resultados finales del proceso se consignarán en el respectivo informe y se reflejarán en la plataforma en la semana 45 del año.
                </p>
            </div>

            <hr class="mt-10 mb-10" />
            <div>
                <p><strong>Tenga en cuenta:</strong> El estado final de los proyectos se conocerá cuando finalice la etapa de segunda evaluación (Estado Rechazado, pre – aprobado con observaciones y Preaprobado). Fechas segunda evaluación: 22 de octubre (13:00 HH) al 3 de noviembre (23:59 HH).</p>
            </div>

            <hr class="mt-10 mb-10" />
            <div>
                <p>Los PDF finales de los proyectos se generará dentro de las próximas 24 horas. Por favor este atento (a).</p>
            </div>
        </div>
        <div slot="actions">
            <div class="p-4">
                <Button variant="raised" on:click={(event) => (dialogOpen = false)}>Entendido</Button>
            </div>
        </div>
    </Dialog> -->
</AuthenticatedLayout>
