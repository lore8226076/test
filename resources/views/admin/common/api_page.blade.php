<nav v-cloak class="mt-4 mb-3" v-if="pageData">
    <ul class="pagination justify-content-center mb-0">
        <!-- <li class="page-item">
            <a class="page-link first" href="#" @click="get('up')">
                <i class="simple-icon-control-start"></i>
            </a>
        </li> -->
        <li class="page-item">
            <a class="page-link prev" href="#" v-if="pageData.current_page > 1" @click.prevent="get('up')">
                <i class="simple-icon-arrow-left"></i>
            </a>
        </li>
        <li class="page-item">
            <select v-model="pageData.current_page" @change.prevent="get()" class="form-control">
                <option v-for="start in pageData.last_page" v-bind:value="start">@{{ start }}</option>
            </select>
        </li>
        <li class="page-item">
            <a class="page-link next" href="#" aria-label="Next" v-if="pageData.current_page < pageData.last_page" @click.prevent="get('down')">
                <i class="simple-icon-arrow-right"></i>
            </a>
        </li>
        <!-- <li class="page-item">
            <a class="page-link last" href="#">
                <i class="simple-icon-control-end"></i>
            </a>
        </li> -->
    </ul>
</nav>