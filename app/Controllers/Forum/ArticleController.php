<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/12/3
 * Time: 下午5:10
 */

namespace App\Controllers\Forum;

use App\Controllers\BaseController;
use App\Models\ForumArticleInfo;

class ArticleController extends BaseController
{
    /**
     * 初始化方法
     */
    public function initialize()
    {
        $this->view->header_choose_type = "forum";
    }

    /**
     * 资讯详情
     * @param $id
     */
    public function detailAction($id)
    {
        $article = ForumArticleInfo::findFirst([
            "conditions" => "id = :article_id: AND status = :status:",
            "bind" => [
                'article_id' => $id,
                'status' => 1
            ],
            'columns' => '*',
        ]);
        $tags = $this->commonConfig->tags->toArray();
        $article->format_time = timeCompute($article->created_time);
        $article->tag_name = $tags[$article->tag];
        $this->view->article = $article;
        $this->view->render("forum", "detail");
    }

    /**
     * 添加资讯页面
     */
    public function addAction()
    {
        $user = $this->user['id'];
        $this->view->tags = $this->commonConfig->tags;
        $this->view->verify_question = setQuestionVerify();
        $this->view->render("forum", "add");
    }

    /**
     * 修改资讯页面
     * @param $articleId
     */
    public function editAction($articleId)
    {
        $user = $this->user['id'];
        $this->view->tags = $this->commonConfig->tags;
        $this->view->verify_question = setQuestionVerify();
        $this->view->render("forum", "add");
    }

    /**
     * 保存文章信息
     */
    public function saveAction()
    {
        if (!$this->request->isPost()) {
            output_data(-502, '非法请求');
        }

        if (!$this->security->checkToken()) {
            output_data(-401, '请刷新页面重新再提交请求');
        }

        $this->user['id'] = 1;
        $title = $this->request->getPost('title');
        $tag = $this->request->getPost('tag', 'int');
        $content = $this->request->getPost('html_content');
        $answer = $this->request->getPost('verify_answer', 'int');
        $articleId = $this->request->getPost('article_id', 'int');
        if (!$title || !in_array($tag, [0, 1, 2]) || !$content || is_null($answer)) {
            output_data(-1, '必要信息不能为空');
        }
        if ($articleId) {
            $article = ForumArticleInfo::findFirst([
                "conditions" => "id = :article_id: AND status = :status: AND user_id = :user_id:",
                "bind" => [
                    'article_id' => $articleId,
                    'user_id' => $this->user['id'],
                    'status' => 1
                ],
                'columns' => '*',
            ]);
            if (!$article) {
                output_data(-1, '你编辑的文章不存在');
            }
        } else {
            $article = new ForumArticleInfo();
            $article->created_time = time();
            $article->user_id = $this->user['id'];
        }
        $article->tag = $tag;
        $article->title = strip_tags($title);
        $article->content = str_replace(['<script>', '</script>'], '', $content);
        $article->updated_time = time();
        if ($article->save()) {
            output_data(1, 'success');
        }
        output_data(-1, '发布失败，请重试');
    }
}