<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import Stepper from '@/Shared/Stepper'
    import SemillerosInvestigacion from './SemillerosInvestigacion'
    import Participantes from './Participantes'

    export let errors
    export let convocatoria
    export let proyecto
    export let tiposDocumento
    export let tiposVinculacion
    export let roles

    $: $title = 'Participantes'
</script>

<AuthenticatedLayout>
    <Stepper {convocatoria} {proyecto} />

    <div class="py-12">
        <div class="mt-16">
            <Participantes {convocatoria} {proyecto} {errors} {tiposDocumento} {tiposVinculacion} {roles} />
        </div>
        <hr class="my-16" />

        <div>
            <SemillerosInvestigacion {convocatoria} {proyecto} {errors} />
        </div>
    </div>
</AuthenticatedLayout>
