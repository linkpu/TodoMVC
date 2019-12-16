<template>
  <div class="todo-box">
    <van-icon name="circle" v-if="item.status == true" class="status-icon" @click="checkStatus(false)" />
    <van-icon name="passed" v-else class="status-icon" @click="checkStatus(true)" />
    <span v-if="!ifShowInput">
      <span v-if="item.status == true" @dblclick="ifShowInput = true;valueChange = item.description ">{{ item.description }}</span>
      <span v-else class="completed" @dblclick="ifShowInput = true;valueChange = item.description">{{ item.description }}</span>
    </span>
    <van-field class="active" v-show="ifShowInput" :atuofocus="true" v-model="valueChange" @keyup.enter="changeDescription"  @blur="changeDescription"></van-field>
    <van-icon name="cross" class="del-icon" @click="delTodo" />
  </div>
</template>

<script>
import { Icon, Field } from "vant";
export default {
  name: "Todo",
  props: {
    item: Object,
    index: Number
  },
  data(){
    return{
      valueChange:'',
      ifShowInput:false
    }
  },
  methods: {
    delTodo() {
      this.$emit("handleDel", this.index);
    },
    checkStatus(status) {
      this.$emit("handleStatus", status, this.index);
    },
    changeDescription(){
      this.$emit('changeDescription',{ index:this.index,value:this.valueChange })
      this.ifShowInput = false;
    }
  },
  components: {
    [Icon.name]: Icon,
    [Field.name]: Field,
  }
}
</script>

<style scoped>
.todo-box {
  padding: 5px;
}
.todo-box span {
  position: relative;
  height: 100%;
  margin-left: 10px;
  top: -1px;
  font-size: 20px;
}
.todo-box .active {
  position: absolute;
  margin-left: 10px;
  height: 50px;
  left: 18px;
  top: -4px;
  border: #FF4500 1px solid;
  display: block;
  font-size: 20px;
}
.todo-box .del-icon {
  position: absolute;
  right: 0;
  top: 7px;
  margin-right: 10px;
  color: #FF4500;
  font-size: 25px;
}
.todo-box .status-icon {
  position: relative;
  left: 0;
  margin-left: -5px;
  top: 3px;
  font-size: 25px;
}
.completed {
  text-decoration: line-through;
  color: #CFCFCF
}
</style>
