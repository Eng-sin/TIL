document.addEventListener('DOMContentLoaded',function(){
  const fields =[
    {
      selector: 'deadlineDate',
      errorId: 'deadlineDateError',
      validate: value => value.trim() === '',
      message: '日付を選択してください'
    },
    {
      selector: 'content',
      errorId: 'contentError',
      validate: value => value.trim() === '',
      message: '内容を入力してください'
    },
    {
      selector: 'taskStatus',
      errorId: 'taskStatusError',
      validate: value => value.trim() === '',
      message: '進捗状況を選択してください'
    },
    {
      selector: 'priority',
      errorId: 'priorityError',
      validate: value => value.trim() === '',
      message: '優先度を選択してください'
    },
    {
      selector: 'publicationRange',
      errorId: 'publicationRangeError',
      validate: value => value.trim() === '',
      message: '公開範囲を選択してください'
    },
  ];

  fields.forEach(field =>{
    const input = document.getElementById(field.selector);
    const error = document.getElementById(field.errorId);

    if(!input || !error) return;
    error.style.display = 'none';

    input.addEventListener('blur',function(){

    if(field.validate(input.value)){
      error.textContent = field.message;
      error.style.display = 'block';
    }else{
      error.textContent = '';
      error.style.display = 'none';
    }
    });
  });
});