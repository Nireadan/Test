<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;

$this->title = 'Catalogs';
$this->params['breadcrumbs'][] = $this->title;

?>

<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">

<div class="site-login" id="CatalogsPage">
    <div class="row" style="margin-bottom: 15px">
        <div class="col-md-7">
            <?= Html::button('Назад', ['class' => 'btn btn-default wf-button', 'onclick' => 'window.history.back()']) ?>
            <button class="btn-info" v-on:click="Getcatalogs">Вывести дерево каталогов</button>
        </div>
    </div>

    <div class="row" style="margin-bottom: 15px">
        <div class="col-md-12">
        </div>
    </div>

    <ul v-if="Catalogs.length != 0">
        <tree-item
                class="item"
                :item="TreeCatalogs[0]"
                @make-folder="makeFolder"
                @add-item="addItem"
                @delete-dir="delItem"
                @edit-dir="editDir"
                @add-child="addItem"
        ></tree-item>
    </ul>

    <modal v-if="showModal" @close="ClearInfo">
        <h3 slot="header">
            <span v-if="modalItem.modalId == 1">Добавить элемент</span>
            <span v-if="modalItem.modalId == 2">Редактировать элемент</span>
            <span v-if="modalItem.modalId == 3">Удаление элемента</span>
        </h3>
        <div slot="body" v-if="modalItem.modalId == 1 || modalItem.modalId == 2">
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group">
                        <label> Название:</label>
                        <input class="form-control wf-input" placeholder="Укажите название каталога"
                               v-model="modalItem.TITLE">
                    </div>
                </div>
                <div class="col-md-4" v-if="modalItem.modalId == 2">
                    <div class="form-group">
                        <label> Родитель:</label>
                        <input class="form-control wf-input" placeholder="Укажите id родителя"
                               v-model="modalItem.PARENTID">
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <button class="btn-success  wf-button" class="close" @click="addChild" v-if="modalItem.modalId == 1">
                    Сохранить
                </button>
                <button class="btn-success  wf-button" class="close" @click="editItem" v-if="modalItem.modalId == 2">
                    Сохранить
                </button>
                <button class="btn btn-default wf-button" class="close" @click="ClearInfo">
                    Закрыть
                </button>
            </div>
        </div>
        <div slot="body" v-if="modalItem.modalId == 3">
            <p style="margin-top: 10pt">Подтвердите удаление каталога "{{modalItem.TITLE}}":</p>
            <button class="btn btn-warning wf-button" @click="deleteDir">
                <i class="fa fa-trash"></i> Подтвердить удаление
            </button>
            <button class="btn btn-default wf-button" @click="ClearInfo">
                <i class="fa fa-minus-circle"></i> Отмена действия
            </button>
        </div>
    </modal>

</div>


<script type="text/x-template" id="modal-template">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">
                        <div class="row" style="margin-bottom: 15px">
                            <div class="col-md-8">
                                <h4 class="ocean-color">
                                    <slot name="header">
                                        header
                                    </slot>
                                </h4>
                            </div>

                            <div class="col-md-4">
                                <button type="button" class="close" @click="$emit('close')">
                                    <span class="fa fa-close"></span>
                                </button>
                            </div>
                        </div>

                        <slot name="body">
                            <b>body</b>
                        </slot>
                    </div>
                </div>
            </div>
        </transition>
</script>

<script type="text/x-template" id="item-template">
    <li>
        <span
                :class="{bold: isFolder}"
                @click="toggle"
                @dblclick="makeFolder">
            {{ item.TITLE }}
            <span v-if="isFolder"> <i :class=folderIcon></i> </span>
        </span>
        <span v-on:click="AddChild" v-if="typeof(item.CHILDREN) == 'undefined'"> <i class="icon-plus"></i> </span>
        <span v-on:click="DeleteDir"> <i class="icon-trash"></i> </span>
        <span v-on:click="EditDir"> <i class="icon-pencil"></i> </span>
        <ul v-show="isOpen" v-if="isFolder">
            <tree-item
                    class="item"
                    v-for="(child, index) in item.CHILDREN"
                    :key="index"
                    :item="child"
                    @make-folder="$emit('make-folder', $event)"
                    @add-item="$emit('add-item', $event)"
                    @delete-dir="$emit('delete-dir', $event)"
                    @edit-dir="$emit('edit-dir', $event)"
                    @add-child="$emit('add-child', $event)"
            ></tree-item>
            <li class="add" @click="$emit('add-item', item)">+</li>
        </ul>
    </li>
</script>

<script>
    CATALOGS = <?= Json::encode($catalogs); ?>;

    Vue.component("tree-item", {
        template: "#item-template",
        props: {
            item: Object
        },
        data: function() {
            return {
                isOpen: false
            };
        },
        computed: {
            isFolder: function() {
                return this.item.CHILDREN && this.item.CHILDREN.length;
            },

            folderIcon: function() {
                if (this.isOpen)
                    return "icon-folder-open";
                else
                    return "icon-folder-close";
            }
        },
        methods: {
            toggle: function() {
                if (this.isFolder) {
                    this.isOpen = !this.isOpen;
                }
            },

            makeFolder: function() {
                if (!this.isFolder) {
                    this.$emit("make-folder", this.item);
                    this.isOpen = true;
                }
            },

            DeleteDir() {
                console.log("Del");
                console.log(this.item.ID);
                this.$emit("delete-dir", this.item);
            },

            EditDir() {
                console.log("Edit");
                console.log(this.item.ID);
                this.$emit("edit-dir", this.item);
            },

            AddChild() {
                console.log("Add");
                console.log(this.item.ID);
                this.$emit("add-child", this.item);
            },
        }
    });

    Vue.component( "modal", {
        template: "#modal-template"
    });

    var CatalogsPage = new Vue({
        el: "#CatalogsPage",
        data: {
            Catalogs: CATALOGS,
            showModal: false,
            modalItem: {},
        },

        methods: {
            /**
             * Получение списка каталогов
             * @constructor
             */
            Getcatalogs() {
                const vm = this;

                $.ajax({
                    type: "GET",
                    url: "?r=site%2Fwork-with-catalog",
                    dataType: "json",
                }).then(function (value) {
                    console.log('Успех: ' + value); // Успех!
                    vm.Catalogs = value;
                }, function (reason) {
                    console.log(reason); // Ошибка!
                });
            },

            /**
             * Раскрытие списка подкаталогов
             * @param item
             */
            makeFolder: function(item) {
                Vue.set(item, "children", []);
                this.addItem(item);
            },

            /**
             * Открывает модальное окно для добавления каталога
             * @param item
             */
            addItem: function(item) {
                const vm = this;
                vm.showModal = true;

                vm.modalItem.DEPTH = Number(item.DEPTH) + 1;
                vm.modalItem.PARENTID = item.ID;
                vm.modalItem.modalId = 1;
            },

            /**
             * Открывает модальное окно для редактирования каталога
             * @param item
             */
            editDir: function(item) {
                const vm = this;
                vm.showModal = true;

                vm.modalItem.ID = item.ID;
                vm.modalItem.TITLE = item.TITLE;
                vm.modalItem.DEPTH = Number(item.DEPTH);
                vm.modalItem.PARENTID = item.PARENTID;

                vm.modalItem.modalId = 2;
            },

            /**
             * Открывает модальное окно для удаления каталога
             * @param item
             */
            delItem: function(item) {
                const vm = this;

                console.log("DeleteItem")
                console.log(item)
                vm.showModal = true;

                vm.modalItem.ID = item.ID;
                vm.modalItem.TITLE = item.TITLE;

                vm.modalItem.modalId = 3;
            },

            /**
             * Отправляет запрос на удаление каталога
             * @param item
             */
            deleteDir: function() {
                const vm = this;

                $.ajax({
                    type: "POST",
                    method: "DELETE",
                    url: "?r=site%2Fwork-with-catalog",
                    data: {Id: vm.modalItem.ID},
                    dataType: "json",
                }).then(function (value) {
                    console.log('Успех: ' + value); // Успех!
                    vm.Catalogs = value;
                    vm.ClearInfo();
                }, function (reason) {
                    console.log(reason); // Ошибка!
                });
            },

            /**
             * Отправляет запрос на добавление каталога
             * @param item
             */
            addChild: function() {
                const vm = this;

                $.ajax({
                    type: "POST",
                    url: "?r=site%2Fwork-with-catalog",
                    data: {item: vm.modalItem},
                    dataType: "json",
                }).then(function (value) {
                    console.log('Успех: ' + value); // Успех!
                    vm.Catalogs = value;
                    vm.ClearInfo();
                }, function (reason) {
                    console.log(reason); // Ошибка!
                });
            },

            /**
             * Отправляет запрос на редактирование каталога
             * @param item
             */
            editItem: function() {
                const vm = this;

                $.ajax({
                    type: "POST",
                    method: "PATCH",
                    url: "?r=site%2Fwork-with-catalog",
                    data: {item: vm.modalItem},
                    dataType: "json",
                }).then(function (value) {
                    console.log('Успех: ' + value); // Успех!
                    vm.Catalogs = value;
                    vm.ClearInfo();
                }, function (reason) {
                    console.log(reason); // Ошибка!
                });
            },

            /**
             * Отчистка данных модального окна
             * @param item
             */
            ClearInfo() {
                const vm = this;
                vm.showModal = false;
                vm.modalItem = {};
            }
        },

        computed: {
            TreeCatalogs() {
                let vm = this;

                let TreeCatalogs = JSON.parse(JSON.stringify(vm.Catalogs));
                let maxDepth = 0;
                TreeCatalogs.forEach((item,index) => {
                    //TreeCatalogs[index].CHILDREN = [];
                    if (item.DEPTH > maxDepth)
                        maxDepth = item.DEPTH
                });

                console.log(maxDepth);

                let generateChild = function () {
                    let delArr = [];
                    TreeCatalogs.forEach((item, index) => {
                        if (item.DEPTH == maxDepth) {
                            let parent = 0;
                            TreeCatalogs.forEach((item1, index1) => {
                                if (item1.ID == item.PARENTID) {
                                    parent = index1;
                                    if (typeof (TreeCatalogs[index1].CHILDREN) == "undefined")
                                        TreeCatalogs[index1].CHILDREN = [];
                                }
                            });
                            if (typeof (parent) !== "undefined") {
                                TreeCatalogs[parent].CHILDREN.push(item);
                                delArr.push(item);
                            }
                        }
                    });

                    delArr.forEach(item => {
                        delIndex = TreeCatalogs.indexOf(item);
                        TreeCatalogs.splice(delIndex, 1);
                    });
                    if (maxDepth != 1) {
                        maxDepth = maxDepth - 1;
                        generateChild();
                    }
                };

                generateChild();
                
                return TreeCatalogs;
            }

        }
    });
</script>

<style>
    .modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: table;
        transition: opacity 0.3s ease;
    }

    .modal-wrapper {
        display: table-cell;
        vertical-align: middle;
    }

    .modal-container {
        width: 50%;
        height: 300px;
        margin: 0px auto;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
        transition: all 0.3s ease;
        font-family: Helvetica, Arial, sans-serif;
    }

    .modal-header h3 {
        margin-top: 0;
        color: #42b983;
    }

    .modal-body {
        margin: 20px 0;
    }

    .modal-default-button {
        float: right;
    }

    /*
     * The following styles are auto-applied to elements with
     * transition="modal" when their visibility is toggled
     * by Vue.js.
     *
     * You can easily play with the modal transition by editing
     * these styles.
     */

    .modal-enter {
        opacity: 0;
    }

    .modal-leave-active {
        opacity: 0;
    }

    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }
</style>
