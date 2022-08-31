<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" v-if="$gate.isAdmin()">
                        <div class="card-header">
                            <h3 class="card-title">Team List</h3>
                            <div class="card-tools">
                                <router-link to="/team" class="btn btn-sm btn-primary">
                                    <i class="fa fa-list"></i>
                                    Team List
                                </router-link>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <form @submit.prevent="createTeam()" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input v-model="form.name" type="text" name="name" class="form-control"
                                            :class="{ 'is-invalid': form.errors.has('name') }">
                                        <has-error :form="form" field="name"></has-error>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <input v-model="form.description" type="text" name="description"
                                            class="form-control"
                                            :class="{ 'is-invalid': form.errors.has('description') }">
                                        <has-error :form="form" field="description"></has-error>
                                    </div>
                                    <div class="form-group">
                                        <input type="file" class="form-control" v-on:change="uploadPhoto"
                                            name="image_src" id="image_src">

                                        <has-error :form="form" field="image_src"></has-error>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>

            <div v-if="!$gate.isAdmin()">
                <not-found></not-found>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            form: new Form({
                id: '',
                name: '',
                description: '',
                image_src: '',
            })
        }
    },
    methods: {
        uploadPhoto(e) {
            let file = e.target.files[0];
            let reader = new FileReader();
            if (file["size"] < 2111775) {
                reader.onloadend = file => {
                    this.form.image_src = reader.result;
                    console.log(this.form.image_src);
                };
                reader.readAsDataURL(file);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'File size can not be bigger than 2 MB'
                });
            }
        },
        createTeam() {
            this.form.post('/api/team')
                .then((response) => {
                    Toast.fire({
                        icon: 'success',
                        title: response.data.message
                    });

                    this.$Progress.finish();

                    this.redirectTeamPage();
                })
                .catch(() => {
                    Toast.fire({
                        icon: 'error',
                        title: 'Some error occured! Please try again'
                    });
                })
        },
        redirectTeamPage() {
            if (this.$gate.isAdmin()) {
                window.location.href = "/team";
            }
        },
    },
    mounted() {
        console.log('Component mounted.')
    },
    created() {
        this.$Progress.start();
        this.$Progress.finish();
    }
}
</script>
