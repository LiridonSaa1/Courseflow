<script setup>
defineProps({
    rows: { type: Number, default: 4 },
});
</script>

<template>
    <div class="students-table-skeleton">
        <v-row align="center" class="students-table-skeleton__head px-4 pt-4 pb-2" density="comfortable">
            <v-col cols="6" sm="3" lg="2">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" sm="3" lg="2">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" sm="3" lg="2">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" sm="3" lg="2">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="12" sm="3" lg="2" class="d-flex justify-center">
                <v-skeleton-loader class="bg-transparent" height="32" type="button" width="32" />
            </v-col>
        </v-row>

        <v-divider class="mx-4" />

        <div class="px-2 px-sm-4 pb-4">
            <v-row
                v-for="n in rows"
                :key="n"
                align="center"
                class="students-table-skeleton__row py-3"
                density="comfortable"
            >
                <v-col cols="6" sm="3" lg="2">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" sm="3" lg="2">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" sm="3" lg="2">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" sm="3" lg="2">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="12" sm="3" lg="2" class="d-flex justify-center">
                    <v-skeleton-loader class="bg-transparent" height="32" type="button" width="32" />
                </v-col>
            </v-row>
        </div>
    </div>
</template>

<style scoped>
.students-table-skeleton {
    min-height: 200px;
}

.students-table-skeleton__row :deep(.v-skeleton-loader__text) {
    max-width: 100%;
}
</style>
