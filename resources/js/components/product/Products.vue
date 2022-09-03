<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-12">

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Blogs List</h3>

                            <div class="card-tools">

                                <button type="button" class="btn btn-sm btn-primary" @click="newModal">
                                    <i class="fa fa-plus-square"></i>
                                    Add New
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in products.data" :key="product.id">

                                        <td>{{ product.id }}</td>
                                        <td>{{ product.name }}</td>
                                        <td>{{ product.description | truncate(30, '...') }}</td>
                                        <td>{{ product.category.name }}</td>
                                        <td>{{ product.price }}</td>
                                        <td><img v-if="product.photo" :src="`upload/product/${product.photo}`"
                                                class="profile-user-img img-fluid img-circle"
                                                style="height:40px; width:40px;" /></td>
                                        <td>

                                            <a href="#" @click="editModal(product)">
                                                <i class="fa fa-edit blue"></i>
                                            </a>
                                            /
                                            <a href="#" @click="deleteProduct(product.id)">
                                                <i class="fa fa-trash red"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <pagination :data="products" @pagination-change-page="getResults"></pagination>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNew" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" v-show="!editmode">Create New Product</h5>
                            <h5 class="modal-title" v-show="editmode">Edit Product</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="editmode ? updateProduct() : createProduct()"
                            enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input v-model="form.name" type="text" name="name" class="form-control"
                                        :class="{ 'is-invalid': form.errors.has('name') }">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea v-model="form.description" type="text" name="description"
                                        class="form-control" :class="{ 'is-invalid': form.errors.has('description') }">
                                    </textarea>
                                    <has-error :form="form" field="description"></has-error>
                                </div>

                                <div class="form-group">

                                    <label>Category</label>
                                    <select class="form-control" v-model="form.category_id">
                                        <option v-for="(cat, index) in categories" :key="index" :value="index"
                                            :selected="index == form.category_id">{{ cat }}</option>
                                    </select>
                                    <has-error :form="form" field="category_id"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Tags</label>
                                    <vue-tags-input v-model="form.tag" :tags="form.tags"
                                        :autocomplete-items="filteredItems"
                                        @tags-changed="newTags => form.tags = newTags" />
                                    <has-error :form="form" field="tags"></has-error>
                                </div>

                                <div class="form-group">
                                    <input type="file" @change="uploadPhoto" name="photo" id="photo"
                                        class="form-control" :class="{ 'is-invalid': form.errors.has('photo') }" />
                                    <has-error :form="form" field="photo"></has-error>
                                </div>
                                <div class="avatar img-fluid img-circle" style="margin-top:10px">
                                    <img :src="getPhoto()" :style="{ height: '100px', width: '100px' }" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button v-show="editmode" type="submit" class="btn btn-success">Update</button>
                                <button v-show="!editmode" type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input';

export default {
    components: {
        VueTagsInput,
    },
    data() {
        return {
            editmode: false,
            products: {},
            form: new Form({
                id: '',
                category: '',
                name: '',
                description: '',
                tags: [],
                photo: '',
                category_id: '',
                photoUrl: '',
            }),
            categories: [],

            tag: '',
            autocompleteItems: [],
        }
    },
    methods: {

        getResults(page = 1) {

            this.$Progress.start();

            axios.get('api/product?page=' + page).then(({ data }) => (this.products = data.data));

            this.$Progress.finish();
        },
        loadProducts() {
            axios.get("api/product").then(({ data }) => (this.products = data.data));
        },
        loadCategories() {
            axios.get("/api/category/list").then(({ data }) => (this.categories = data.data));
        },
        loadTags() {
            axios.get("/api/tag/list").then(response => {
                this.autocompleteItems = response.data.data.map(a => {
                    return { text: a.name, id: a.id };
                });
            }).catch(() => console.warn('Oh. Something went wrong'));
        },
        uploadPhoto(e) {
            let file = e.target.files[0];
            let reader = new FileReader();

            if (file["size"] < 2111775) {
                reader.onloadend = file => {
                    this.form.photo = reader.result;
                };
                reader.readAsDataURL(file);
            } else {
                alert("File size can not be bigger than 2 MB");
            }
        },

        getPhoto() {
            if (this.form.photo) {
                let photo =
                    this.form.photo.length > 100
                        ? this.form.photo
                        : "upload/product/" + this.form.photo;
                return photo;
            }
        },
        editModal(product) {
            this.editmode = true;
            this.form.reset();
            $('#addNew').modal('show');
            this.form.fill(product);
        },
        newModal() {
            this.editmode = false;
            this.form.reset();
            $('#addNew').modal('show');
        },
        createProduct() {
            this.$Progress.start();

            this.form.post('api/product')
                .then((data) => {
                    if (data.data.success) {
                        $('#addNew').modal('hide');

                        Toast.fire({
                            icon: 'success',
                            title: data.data.message
                        });
                        this.$Progress.finish();
                        this.loadProducts();

                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Some error occured! Please try again'
                        });

                        this.$Progress.failed();
                    }
                })
                .catch(() => {

                    Toast.fire({
                        icon: 'error',
                        title: 'Some error occured! Please try again'
                    });
                })
        },
        updateProduct() {
            this.$Progress.start();
            this.form.put('api/product/' + this.form.id)
                .then((response) => {

                    $('#addNew').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.data.message
                    });
                    this.$Progress.finish();


                    this.loadProducts();
                })
                .catch(() => {
                    this.$Progress.fail();
                });

        },
        deleteProduct(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {


                if (result.value) {
                    this.form.delete('api/product/' + id).then(() => {
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        );

                        this.loadProducts();
                    }).catch((data) => {
                        Swal.fire("Failed!", data.message, "warning");
                    });
                }
            })
        },

    },
    mounted() {

    },
    created() {
        this.$Progress.start();

        this.loadProducts();
        this.loadCategories();
        this.loadTags();

        this.$Progress.finish();
    },
    filters: {
        truncate: function (text, length, suffix) {
            return text.substring(0, length) + suffix;
        },
    },
    computed: {
        filteredItems() {
            return this.autocompleteItems.filter(i => {
                return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
            });
        },
    },
}
</script>
