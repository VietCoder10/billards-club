import $ from 'jquery';
import axios from 'axios';
import kuromoji from 'kuromoji';

let tokenizerPromise = null;

export const getTokenizer = () => {
  if (tokenizerPromise) return tokenizerPromise;

  tokenizerPromise = new Promise((resolve, reject) => {
    kuromoji.builder({ dicPath: '/dict/' }).build((err, tokenizer) => {
      if (err) {
        reject(err);
      } else {
        resolve(tokenizer);
      }
    });
  });

  return tokenizerPromise;
};

export const getKanji = async (str) => {
  let res = await getTokenizer().then((tokenizer) => tokenizer.tokenize(str));
  return res.map((token) => token.reading).join('');
};

export const kataToHira = (str) => {
  return str.replace(/[\u30a1-\u30f6]/g, function (match) {
    var chr = match.charCodeAt(0) - 0x60;
    return String.fromCharCode(chr);
  });
};

export const getHiragana = async (str) => {
  let res = await getTokenizer().then((tokenizer) => tokenizer.tokenize(str));
  // let val = res.map((token) => token.reading || token.surface_form).join('');
  let val = res.map((token) => token.reading).join('');
  return kataToHira(val);
};
export const calcSummary = (items = [], table_total = 0) => {
  const service_total = items.reduce((sum, item) => sum + Number(item.sub_total || 0), 0);

  const final_total = service_total + Number(table_total || 0);

  return {
    service_total,
    table_total: Number(table_total || 0),
    final_total: final_total
  };
};
export const calcPrice = (price, quantity, sub_total) => {
  if (!quantity) {
    return {
      price: price,
      quantity: 0,
      sub_total: 0
    };
  }
  if (quantity) {
    return {
      price: price,
      quantity: quantity,
      sub_total: Math.round(price * quantity)
    };
  } else {
    if (!price) {
      return {
        price: null,
        quantity: null,
        sub_total: null
      };
    }
    return {
      price: price,
      quantity: 0,
      sub_total: 0
    };
  }
};
export const getErrorCount = (tabValue, errors) => {
  const keys = Object.keys(errors);
  let countError = 0;
  keys.forEach((key) => {
    let ele = $('[name="' + key + '"]');
    if (ele.closest('.parent-tab-panel').attr('id') === tabValue) {
      countError++;
    }
  });
  return countError;
};

export const getNestedErrorCount = (tabValue, errors) => {
  const keys = Object.keys(errors);
  let countError = 0;
  keys.forEach((key) => {
    let ele = $('[name="' + key + '"]');
    if (ele.closest('.parent-tab-panel[id="' + tabValue + '"]').length > 0) {
      countError++;
    }
  });
  return countError;
};

export const fillAddressFromPostalCode = async ({ postCode }) => {
  try {
    const { data } = await axios.get('https://zipcloud.ibsnet.co.jp/api/search', {
      params: { zipcode: String(postCode).replace(/[^0-9]/g, '') }
    });
    if (data?.status === 200 && data?.results?.length) {
      const r = data.results[0];
      return {
        prefecture_name: r.address1,
        city: r.address2 + r.address3
      };
    }
    return {
      prefecture_name: '',
      city: ''
    };
  } catch (e) {
    return {
      prefecture_name: '',
      city: ''
    };
  }
};
export const calculateAge = (birthday) => {
  if (!birthday) {
    return null;
  }
  const today = new Date();
  const birthDate = new Date(birthday);
  let age = today.getFullYear() - birthDate.getFullYear();
  const month = today.getMonth() - birthDate.getMonth();
  if (month < 0 || (month === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age;
};
export const formatSize = (bytes) => {
  const k = 1024;
  const dm = 3;
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

  if (bytes === 0) {
    return `0 ${sizes[0]}`;
  }

  const i = Math.floor(Math.log(bytes) / Math.log(k));
  const formattedSize = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));

  return `${formattedSize} ${sizes[i]}`;
};
export const calcAgeFromBirthday = (birthday) => {
  if (!birthday) {
    return null;
  }
  const today = new Date();
  const birthDate = new Date(birthday);
  let age = today.getFullYear() - birthDate.getFullYear();
  const month = today.getMonth() - birthDate.getMonth();
  if (month < 0 || (month === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }

  return age < 0 ? null : age;
};
