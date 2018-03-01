#!/usr/bin/python
# -*- coding: utf-8 -*-
import requests
from bs4 import BeautifulSoup
import MySQLdb
import threading,time
# 打开数据库连接
db = MySQLdb.connect("rm-wz9a57t95y5ozx31ho.mysql.rds.aliyuncs.com","cys_cgj","cys_cgjAccess","cys_cgj" )

# 使用cursor()方法获取操作游标 
cursor = db.cursor()

# 使用execute方法执行SQL语句
cursor.execute("SELECT brand_id,cate_id,autohome_id,car_id from cys_car_category")
results = cursor.fetchall()


threads = []
num = 200



def save_car_info(car_autohome_id, brand_id, cate_id, car_id):
	res = requests.get('https://m.autohome.com.cn/'+ str(car_autohome_id) +'/')
	# print(res.content)

	soup = BeautifulSoup(res.content,'html.parser')
	res = soup.find_all(class_="caption")
	reslist = soup.find_all(class_="module-list-cartype")
	# for x in res:
	# 	print(x.string)
	test = []
	for j, x in enumerate(reslist):
		tmp = {}
		h4 = x.find_all('h4')
		tmp['name'] = res[j].text
		tmp['list'] = []
		guide = x.find_all(class_ = 'guide')
		lowest = x.find_all(class_ = 'lowest')
		for i in xrange(0,len(h4)):
			specid = 0
			# print(lowest)
			if 'data-specid' in lowest[i].attrs:
				specid = lowest[i].attrs['data-specid']
			# test.append({
			# 	'name': h4[i].find('a').text,
			# 	'specid': specid,
			# 	'guide': guide[i].text.replace(u'厂商指导价：', '').replace(u'万', ''),
			# 	'year' : h4[i].find('a').text[0:4],
			# 	'capacity': res[j].text[0:3],
			# 	'horsepower': res[j].text[6:].replace(u'马力', '')
			# })

			name = h4[i].find('a').text
			guide1 = guide[i].text.replace(u'厂商指导价：', '').replace(u'万', '')
			year = h4[i].find('a').text[0:4]
			capacity = res[j].text[0:3]
			horsepower = res[j].text[6:].replace(u'马力', '')
			sql = 'insert into cys_car_info_extra (name, specid, guide, year, capacity, horsepower,brand_id, cate_id, car_id, car_autohome_id) values("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")' % (name,specid,guide1,year,capacity,horsepower,brand_id, cate_id, car_id, car_autohome_id)
			print(sql)
			cursor.execute(sql)
			db.commit()  
			# print(name, specid, guide1, year, capacity, horsepower)
			# print(h4[i].find('a').text)
			# print(url)
			# print(value)
			# print(guide[i].text)
			# print(lowest[i])
			# if  lowest[i].attrs['data-specid']
		# print('------------------')
		# s = BeautifulSoup(x,'html.parser')
		# href = s.a
		# print(href)
		test.append(tmp)
	print(test)


for x in results:
	save_car_info(x[2],x[0],x[1],x[3])


# def save_all_car_info(car_list):
# 	for x in car_list:
# 		save_car_info(x[2],x[0],x[1],x[3])



# for i in range(0,len(results),num):
# 	list_item = results[i:i+num]
# 	t2 = threading.Thread(target=save_all_car_info,args=(list_item,))
# 	threads.append(t2)
# for t in threads:
# 	t.start()
