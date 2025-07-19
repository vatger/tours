<template>
    <div class="flex flex-col space-y-4">
        <slot name="header"></slot>

        <!-- Filtros -->
        <div class="flex flex-col gap-4 mb-2 sm:flex-row sm:justify-between">
            <slot name="filters"></slot>
        </div>

        <!-- Conteúdo principal -->
        <div class="border rounded-md">
            <slot></slot>
        </div>

        <!-- Paginação -->
        <div class="flex flex-col gap-4 mt-6 sm:flex-row sm:items-center sm:justify-between">
            <slot name="pagination-info"></slot>
            <slot name="pagination-controls"></slot>
        </div>
    </div>
</template>
