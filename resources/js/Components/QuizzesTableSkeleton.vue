<script setup>
defineProps({
    rows: { type: Number, default: 5 },
});
</script>

<template>
    <div class="quizzes-table-skeleton">
        <v-row align="center" class="px-4 pt-4 pb-2" density="comfortable">
            <v-col cols="12" md="2">
                <v-skeleton-loader class="bg-transparent" type="heading" />
            </v-col>
            <v-col cols="6" md="2">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" md="1">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" md="1">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" md="1">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" md="1">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="6" md="2">
                <v-skeleton-loader class="bg-transparent" type="text" />
            </v-col>
            <v-col cols="12" md="1" class="d-flex justify-center">
                <v-skeleton-loader class="bg-transparent" height="32" type="button" width="32" />
            </v-col>
        </v-row>

        <v-divider class="mx-4" />

        <div class="px-2 px-sm-4 pb-4">
            <v-row
                v-for="n in rows"
                :key="n"
                align="center"
                class="py-3"
                density="comfortable"
            >
                <v-col cols="12" md="2">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" md="2">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" md="1">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" md="1">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" md="1">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" md="1">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="6" md="2">
                    <v-skeleton-loader class="bg-transparent" type="text" />
                </v-col>
                <v-col cols="12" md="1" class="d-flex justify-center">
                    <v-skeleton-loader class="bg-transparent" height="32" type="button" width="32" />
                </v-col>
            </v-row>
        </div>
    </div>
</template>

<style scoped>
.quizzes-table-skeleton {
    min-height: 200px;
}
</style>
