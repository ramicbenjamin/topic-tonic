<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import TopicController from '@/actions/App/Http/Controllers/TopicController';

import { Head, useForm } from '@inertiajs/vue3';

import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';

interface TopicComment {
    id: number;
    body: string;
    author_name: string;
    created_human: string;
    can_delete: boolean;
}

interface Topic {
    id: number;
    title: string;
    body: string;
    author_name: string;
    created_human: string;
    comments: TopicComment[];
    comments_count: number;
    likes_count: number;
    viewer_has_liked: boolean;
}

const props = defineProps<{
    topic: Topic;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Topics',
        href: TopicController.index().url,
    },
    {
        title: props.topic.title,
        href: TopicController.show({ topic: props.topic.id }).url,
    },
];

// Simple Inertia form for adding comments
const commentForm = useForm({
    body: '',
});

const submitComment = () => {
    commentForm.post(`/topics/${props.topic.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset('body');
        },
    });
};

const deleteComment = (comment: TopicComment) => {
    if (!comment.can_delete) return;

    commentForm.delete(`/topics/${props.topic.id}/comments/${comment.id}`, {
        preserveScroll: true,
    });
};

const likeTopic = () => {
    commentForm.post(`/topics/${props.topic.id}/likes`, {
        preserveScroll: true,
        preserveState: true,
    });
};

const unlikeTopic = () => {
    commentForm.delete(`/topics/${props.topic.id}/likes`, {
        preserveScroll: true,
        preserveState: true,
    });
};

const initials = (name: string): string => {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="topic.title" />

        <div class="flex flex-1 flex-col gap-4 overflow-x-hidden rounded-xl p-4">
            <div class="grid gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1.3fr)]">
                <!-- Topic card -->
                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader class="space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <CardTitle class="text-lg md:text-xl">
                                    {{ topic.title }}
                                </CardTitle>
                                <CardDescription class="flex flex-wrap items-center gap-2 text-xs md:text-sm">
                                    <span class="text-foreground/80">
                                        Posted by
                                        <span class="font-medium">{{ topic.author_name }}</span>
                                    </span>
                                    <span class="text-muted-foreground">•</span>
                                    <span class="text-muted-foreground">
                                        {{ topic.created_human }}
                                    </span>
                                </CardDescription>
                            </div>

                            <Badge variant="outline" class="text-[10px] md:text-xs">
                                Topic Tonic
                            </Badge>
                        </div>
                    </CardHeader>

                    <Separator />

                    <CardContent class="pt-4 text-sm leading-relaxed md:text-[15px]">
                        <p class="whitespace-pre-line text-foreground/90">
                            {{ topic.body }}
                        </p>
                    </CardContent>

                    <CardFooter class="flex flex-wrap items-center justify-between gap-3 border-t border-sidebar-border/40 bg-muted/40 py-3 text-xs md:text-sm">
                        <div class="flex items-center gap-3">
                            <div class="inline-flex items-center gap-1.5 text-muted-foreground/90">
                                <span class="text-base">💬</span>
                                <span class="font-medium">{{ topic.comments_count }}</span>
                                <span class="hidden sm:inline">comments</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 text-muted-foreground/90">
                                <span class="text-base">✨</span>
                                <span class="font-medium">{{ topic.likes_count }}</span>
                                <span class="hidden sm:inline">likes</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                v-if="!topic.viewer_has_liked"
                                size="sm"
                                variant="outline"
                                class="gap-1.5"
                                :disabled="commentForm.processing"
                                @click="likeTopic"
                            >
                                <span>✨</span>
                                <span>Like this topic</span>
                            </Button>

                            <Button
                                v-else
                                size="sm"
                                variant="outline"
                                class="gap-1.5"
                                :disabled="commentForm.processing"
                                @click="unlikeTopic"
                            >
                                <span>💤</span>
                                <span>Unlike</span>
                            </Button>
                        </div>
                    </CardFooter>
                </Card>

                <!-- Comment form -->
                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg">
                            Add your take
                        </CardTitle>
                        <CardDescription>
                            Share a quick thought, reaction, or counter-point on this topic.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form class="space-y-3" @submit.prevent="submitComment">
                            <div class="space-y-2">
                                <Textarea
                                    v-model="commentForm.body"
                                    name="body"
                                    rows="4"
                                    placeholder="What’s your tonic on this topic?"
                                    class="resize-none"
                                />
                                <InputError :message="commentForm.errors.body" />
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs text-muted-foreground">
                                <span class="hidden sm:inline">
                                    Be kind, concise, and on point. No spam, no drama.
                                </span>

                                <Button
                                    size="sm"
                                    :disabled="commentForm.processing || !commentForm.body.trim()"
                                >
                                    Post comment
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Comments list -->
            <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                <CardHeader class="flex flex-row items-center justify-between gap-2">
                    <div>
                        <CardTitle class="text-base md:text-lg">
                            Conversation
                        </CardTitle>
                        <CardDescription>
                            {{ topic.comments_count === 0
                            ? 'No comments yet. Be the first to drop a tonic.'
                            : topic.comments_count === 1
                                ? '1 comment on this topic.'
                                : `${topic.comments_count} comments on this topic.` }}
                        </CardDescription>
                    </div>
                </CardHeader>

                <Separator v-if="topic.comments.length > 0" />

                <CardContent v-if="topic.comments.length > 0" class="divide-y divide-sidebar-border/40">
                    <div
                        v-for="comment in topic.comments"
                        :key="comment.id"
                        class="flex gap-3 py-3 md:py-4"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold uppercase text-foreground/80"
                        >
                            {{ initials(comment.author_name) }}
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col gap-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-foreground">
                                        {{ comment.author_name }}
                                    </span>
                                    <span class="text-[11px] uppercase tracking-wide text-muted-foreground/80">
                                        {{ comment.created_human }}
                                    </span>
                                </div>

                                <Button
                                    v-if="comment.can_delete"
                                    variant="ghost"
                                    size="xs"
                                    class="h-7 px-2 text-[11px] text-muted-foreground hover:text-destructive"
                                    :disabled="commentForm.processing"
                                    @click="deleteComment(comment)"
                                >
                                    Remove
                                </Button>
                            </div>

                            <p class="whitespace-pre-line text-sm leading-relaxed text-foreground/90">
                                {{ comment.body }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
