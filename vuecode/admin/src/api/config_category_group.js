import request from '@/utils/request'
import apiArr from '@/api'

export function apiList(params) {
  return request({
    url: apiArr.configCategoryGroup,
    method: 'get',
    params
  })
}
export function apiAdd(data) {
  return request({
    url: apiArr.configCategoryGroupAdd,
    method: 'post',
    data
  })
}
export function apiEdit(params, method = 'post') {
  if (method == 'post') {
    return request({
      url: apiArr.configCategoryGroupEdit,
      method,
      data: params
    })
  } else {
    return request({
      url: apiArr.configCategoryGroupEdit,
      method,
      params
    })
  }
}
export function apiDelete(data) {
  return request({
    url: apiArr.configCategoryGroupDelete,
    method: 'post',
    data
  })
}
