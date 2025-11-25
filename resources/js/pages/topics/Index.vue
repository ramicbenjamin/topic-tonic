<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import TopicController from '@/actions/App/Http/Controllers/TopicController';
import { dashboard } from '@/routes';
import { index as topicsIndex, show as topicsShow } from '@/routes/topics';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Topic {
    id: number;
    title: string;
    body: string | null;
    author: string | null;
    created_human: string;
}

const props = defineProps<{
    topics: {
        data: Topic[];
    };
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'My topics',
        href: topicsIndex().url,
    },
];

const form = useForm({
    title: '',
    body: '',
});

const submit = () => {
    form.post(TopicController.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('title', 'body');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="My topics" />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <!-- Page header -->
            <div class="flex flex-col gap-1">
                <h1 class="text-lg font-semibold tracking-tight">
                    My topics
                </h1>
                <p class="text-sm text-muted-foreground">
                    Create topics to capture ideas, then revisit them to see how the discussion unfolds.
                </p>
            </div>

            <!-- Create topic (top) -->
            <Card
                class="border-sidebar-border/70 dark:border-sidebar-border"
                data-test="topics-create-card"
            >
                <CardHeader>
                    <CardTitle class="text-base">
                        Create a new topic
                    </CardTitle>
                    <CardDescription>
                        Share what is on your mind to start a conversation.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                name="title"
                                v-model="form.title"
                                placeholder="What’s on your mind?"
                                autocomplete="off"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="body">Details (optional)</Label>
                            <Textarea
                                id="body"
                                name="body"
                                v-model="form.body"
                                rows="4"
                                placeholder="Add a bit more context to kickstart the discussion..."
                            />
                            <InputError class="mt-1" :message="form.errors.body" />
                        </div>

                        <div class="flex items-center gap-3">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                data-test="topics-create-button"
                            >
                                <span v-if="!form.processing">Post topic</span>
                                <span v-else>Posting...</span>
                            </Button>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="form.recentlySuccessful"
                                    class="text-xs text-neutral-600"
                                >
                                    Topic posted.
                                </p>
                            </Transition>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- My topics list -->
            <Card
                class="border-sidebar-border/70 dark:border-sidebar-border"
                data-test="topics-list-card"
            >
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">My recent topics</CardTitle>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Only topics you created are shown here.
                        </p>
                    </div>
                </CardHeader>

                <CardContent class="space-y-3">
                    <div
                        v-if="!props.topics.data.length"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        You have not created any topics yet. Start with one above.
                    </div>

                    <div
                        v-else
                        class="divide-y divide-border/70 dark:divide-sidebar-border"
                    >
                        <div
                            v-for="topic in props.topics.data"
                            :key="topic.id"
                            class="py-3 flex flex-col gap-1"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <Link
                                    :href="topicsShow(topic.id).url"
                                    class="font-medium text-sm hover:underline"
                                >
                                    {{ topic.title }}
                                </Link>
                            </div>

                            <p class="text-xs text-muted-foreground line-clamp-2">
                                {{ topic.body || 'No additional details.' }}
                            </p>

                            <p class="text-[11px] text-muted-foreground">
                                {{ topic.created_human }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
