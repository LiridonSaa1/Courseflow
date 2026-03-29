<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    backHref: { type: String, default: '/dashboard' },
    /** Page title next to back (e.g. List of Courses) */
    listTitle: { type: String, default: 'List of Courses' },
    /** Link to archive / trash page */
    archiveHref: { type: String, default: '/courses/archive' },
    /** Checkbox selection count — dot on Edit / Trash when non-zero */
    selectedCount: { type: Number, default: 0 },
    /** Trashed count — dot on Archive icon when non-zero */
    archivedCount: { type: Number, default: 0 },
    createTooltip: { type: String, default: 'Create course' },
    editTooltip: { type: String, default: 'Edit course' },
    deleteTooltip: { type: String, default: 'Move to archive (trash)' },
    archiveTooltip: { type: String, default: 'Open archive (trash)' },
    filterTooltip: { type: String, default: 'Filter courses' },
    /** When false, hide archive (e.g. course show has no module archive). */
    showArchive: { type: Boolean, default: true },
    /** When false, hide filter button. */
    showFilter: { type: Boolean, default: true },
});

const emitEvents = defineEmits(['create', 'edit', 'delete', 'filter']);

function onPlusClick() {
    emitEvents('create');
}

function onEditClick() {
    emitEvents('edit');
}

function onDeleteClick() {
    emitEvents('delete');
}

function onFilterClick() {
    emitEvents('filter');
}
</script>

<template>
    <v-toolbar class="table-toolbar px-2 px-sm-4" color="primary" density="comfortable" flat>
        <Link
            class="d-flex align-center ga-2 flex-shrink-0 text-decoration-none text-white"
            :href="backHref"
        >
            <v-btn color="white" icon="mdi-arrow-left" variant="text" />
            <span class="text-body-1 font-weight-medium">{{ listTitle }}</span>
        </Link>

        <v-spacer />

        <div class="d-flex align-center flex-shrink-0 ga-1">
            <v-tooltip location="top" :text="createTooltip">
                <template #activator="{ props: tooltipProps }">
                    <!-- span = activator so v-btn click is separate from tooltipProps (fixes open create drawer) -->
                    <span v-bind="tooltipProps" class="d-inline-flex align-center">
                        <v-btn
                            color="white"
                            icon="mdi-plus"
                            variant="text"
                            @click="onPlusClick"
                        />
                    </span>
                </template>
            </v-tooltip>
            <v-tooltip location="top" :text="editTooltip">
                <template #activator="{ props: tooltipProps }">
                    <span v-bind="tooltipProps" class="d-inline-flex align-center table-toolbar-icon-wrap">
                        <v-badge
                            class="table-toolbar-action-badge"
                            color="warning"
                            dot
                            location="top end"
                            :model-value="selectedCount > 0"
                            offset-x="6"
                            offset-y="6"
                        >
                            <v-btn color="white" icon="mdi-pencil-outline" variant="text" @click="onEditClick" />
                        </v-badge>
                    </span>
                </template>
            </v-tooltip>
            <v-tooltip location="top" :text="deleteTooltip">
                <template #activator="{ props: tooltipProps }">
                    <span v-bind="tooltipProps" class="d-inline-flex align-center table-toolbar-icon-wrap">
                        <v-badge
                            class="table-toolbar-action-badge"
                            color="warning"
                            dot
                            location="top end"
                            :model-value="selectedCount > 0"
                            offset-x="6"
                            offset-y="6"
                        >
                            <v-btn color="white" icon="mdi-delete-outline" variant="text" @click="onDeleteClick" />
                        </v-badge>
                    </span>
                </template>
            </v-tooltip>

            <v-divider
                v-if="showArchive || showFilter"
                class="table-toolbar-divider mx-1 align-self-center"
                inset
                length="24"
                thickness="2"
                vertical
            />
            <v-tooltip v-if="showArchive" location="top" :text="archiveTooltip">
                <template #activator="{ props: tooltipProps }">
                    <Link class="d-inline-flex text-decoration-none" :href="archiveHref">
                        <span v-bind="tooltipProps" class="d-inline-flex align-center table-toolbar-icon-wrap">
                            <v-badge
                                class="table-toolbar-action-badge"
                                color="error"
                                dot
                                location="top end"
                                :model-value="archivedCount > 0"
                                offset-x="8"
                                offset-y="6"
                            >
                                <v-btn color="white" icon="mdi-archive-plus-outline" variant="text" />
                            </v-badge>
                        </span>
                    </Link>
                </template>
            </v-tooltip>

            <v-tooltip v-if="showFilter" location="top" :text="filterTooltip">
                <template #activator="{ props: tooltipProps }">
                    <span v-bind="tooltipProps" class="d-inline-flex align-center">
                        <v-btn color="white" icon="mdi-magnify" variant="text" @click="onFilterClick" />
                    </span>
                </template>
            </v-tooltip>
        </div>

        <div class="d-flex align-center flex-shrink-0">
            <slot name="append" />
        </div>
    </v-toolbar>
</template>

<style scoped>
.table-toolbar {
    align-items: center;
}

.table-toolbar :deep(.v-toolbar__content) {
    align-items: center;
}

.table-toolbar-icon-wrap {
    line-height: 0;
}

.table-toolbar-action-badge :deep(.v-badge__badge) {
    min-width: 10px;
    min-height: 10px;
    height: 10px;
    width: 10px;
    border: 2px solid rgb(var(--v-theme-primary));
    box-sizing: content-box;
}

.table-toolbar-divider {
    opacity: 0.45;
    border-color: rgb(255 255 255 / 0.5);
}

.table-toolbar-search {
    min-width: 160px;
    max-width: min(320px, 42vw);
}

@media (max-width: 600px) {
    .table-toolbar-search {
        min-width: 120px;
        max-width: 36vw;
    }
}
</style>
