<template>
    <div>
        <editor-menu-bar v-if="active" :editor="editor" v-slot="{ commands, isActive }">
            <div>
                <v-btn x-small :dark="darkmode" @click="commands.bold">
                    <v-icon x-small>far fa-bold</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.italic">
                    <v-icon x-small>far fa-italic</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.underline">
                    <v-icon x-small>far fa-underline</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.bullet_list">
                    <v-icon x-small>far fa-list</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.ordered_list">
                    <v-icon x-small>far fa-list-ol</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.code">
                    <v-icon x-small>far fa-code</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.code">
                    <v-icon x-small>far fa-quote-right</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.code">
                    <v-icon x-small>far fa-undo</v-icon>
                </v-btn>
                <v-btn x-small :dark="darkmode" @click="commands.code">
                    <v-icon x-small>far fa-redo</v-icon>
                </v-btn>
            </div>
        </editor-menu-bar>
        <editor-content :editor="editor"></editor-content>
    </div>
</template>

<script>

    import { Editor, EditorContent, EditorMenuBar } from 'tiptap';
    import {
      Blockquote,
      CodeBlock,
      Heading,
      OrderedList,
      BulletList,
      ListItem,
      Bold,
      Code,
      Italic,
      Link,
      Underline,
      History,
    } from 'tiptap-extensions'

    export default {

        name: "richtext ",
        props: ['content'],
        components: {
            EditorMenuBar,
            EditorContent,
        },
        data() {
            return {
                editor: null,
            }
        },
        computed: {
            active () {
                if (typeof this.$attrs.active != typeof undefined) return this.$attrs.active;
                else return true;
            },
            darkmode () {
                return this.$store.state.darkmode;
            }
        },
        mounted() {
            var self = this;
            this.editor = new Editor({
                extensions: [
                    new Blockquote(),
                    new CodeBlock(),
                    new Heading({ levels: [1, 2, 3] }),
                    new BulletList(),
                    new OrderedList(),
                    new ListItem(),
                    new Bold(),
                    new Code(),
                    new Italic(),
                    new Link(),
                    new Underline(),
                    new History(),
                ],
                content: self.content,
                onUpdate: ({ getHTML }) => {
                    // get new content on update
                    self.$emit('input', getHTML());
                },
            })
        },
        beforeDestroy() {
            this.editor.destroy();
        },
    }
</script>

<style lang="scss" scoped>

</style>
